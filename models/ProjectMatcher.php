<?php

class ProjectMatcher {
    private $conn;
    private $cache_table = 'project_match_cache';
    private $action_table = 'user_match_action';
    
    // Matching weights
    private $SKILL_WEIGHT = 50;      // 50% of score from skills
    private $INTEREST_WEIGHT = 30;   // 30% of score from interests
    private $ACTIVITY_WEIGHT = 20;   // 20% of score from activity level
    
    // Cache duration in seconds (1 hour)
    private $CACHE_DURATION = 3600;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Calculate match score between user and project
     * 
     * Formula:
     * - Skill Match (50%): user_matching_skills / project_required_skills * 100
     * - Interest Match (30%): is category in user interests? 100 : 0
     * - Activity Score (20%): (user_activity / avg_activity) * 100
     * 
     * Final Score = (skill_score * 0.5) + (interest_score * 0.3) + (activity_score * 0.2)
     */
    public function calculateMatchScore($user_id, $projet_id) {
        // Get skill match
        $skill_score = $this->calculateSkillMatch($user_id, $projet_id);
        
        // Get interest match
        $interest_score = $this->calculateInterestMatch($user_id, $projet_id);
        
        // Get activity score
        $activity_score = $this->calculateActivityScore($user_id);
        
        // Calculate weighted final score
        $final_score = ($skill_score * ($this->SKILL_WEIGHT / 100)) +
                      ($interest_score * ($this->INTEREST_WEIGHT / 100)) +
                      ($activity_score * ($this->ACTIVITY_WEIGHT / 100));
        
        return min(100, max(0, round($final_score, 2)));
    }

    /**
     * Calculate skill match percentage (50% weight)
     * Compares user skills vs project required skills
     */
    private function calculateSkillMatch($user_id, $projet_id) {
        // Get project required skills
        $query_project = "SELECT COUNT(*) as count FROM project_skill WHERE projet_id = ? AND required = 1";
        $stmt = $this->conn->prepare($query_project);
        $stmt->execute([$projet_id]);
        $project_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $required_skills_count = $project_result['count'];

        // If no required skills, give full marks
        if ($required_skills_count == 0) {
            return 100;
        }

        // Get matching skills (user has project's required skills)
        $query_match = "
            SELECT COUNT(DISTINCT ps.skill_id) as matched_count
            FROM project_skill ps
            INNER JOIN user_skill us ON ps.skill_id = us.skill_id
            WHERE ps.projet_id = ? AND ps.required = 1 AND us.user_id = ?
        ";
        $stmt = $this->conn->prepare($query_match);
        $stmt->execute([$projet_id, $user_id]);
        $match_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $matched_skills_count = $match_result['matched_count'];

        // Calculate percentage
        $skill_match = ($matched_skills_count / $required_skills_count) * 100;
        return round($skill_match, 2);
    }

    /**
     * Calculate interest match percentage (30% weight)
     * Checks if project category is in user interests
     */
    private function calculateInterestMatch($user_id, $projet_id) {
        // Get project category
        $query_project = "SELECT categorie_id FROM projet WHERE id = ?";
        $stmt = $this->conn->prepare($query_project);
        $stmt->execute([$projet_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            return 0;
        }

        // Check if user interested in this category
        $query_interest = "SELECT interest_score FROM user_interest WHERE user_id = ? AND categorie_id = ?";
        $stmt = $this->conn->prepare($query_interest);
        $stmt->execute([$user_id, $project['categorie_id']]);
        $interest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($interest) {
            // Convert 1-5 score to 0-100
            $score = ($interest['interest_score'] / 5) * 100;
            return round($score, 2);
        }

        return 0;
    }

    /**
     * Calculate activity score (20% weight)
     * Based on user's engagement level (posts, reactions, comments)
     */
    private function calculateActivityScore($user_id) {
        // Count user activities
        $query = "
            SELECT 
                COALESCE((SELECT COUNT(*) FROM post WHERE auteur_id = ?), 0) as posts_count,
                COALESCE((SELECT COUNT(*) FROM reaction WHERE user_id = ?), 0) as reactions_count,
                COALESCE((SELECT COUNT(*) FROM commentaire WHERE auteur_id = ?), 0) as comments_count,
                COALESCE((SELECT COUNT(*) FROM projet_reaction WHERE user_id = ?), 0) as projet_reactions_count,
                COALESCE((SELECT COUNT(*) FROM projet_commentaire WHERE auteur_id = ?), 0) as projet_comments_count
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
        $activity = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate total activity points
        $total_activity = ($activity['posts_count'] * 10) +
                         ($activity['reactions_count'] * 1) +
                         ($activity['comments_count'] * 2) +
                         ($activity['projet_reactions_count'] * 1) +
                         ($activity['projet_comments_count'] * 2);

        // Get average activity across all users
        $query_avg = "
            SELECT AVG(activity_score) as avg_score FROM (
                SELECT 
                     (COUNT(p.id_post) * 10 + COUNT(pr.id_reaction) * 1 + COUNT(c.id_commentaire) * 2 + 
                         COUNT(pjr.id) * 1 + COUNT(pjc.id) * 2) as activity_score
                FROM utilisateur u
                LEFT JOIN post p ON u.id_utilisateur = p.auteur_id
                LEFT JOIN reaction pr ON u.id_utilisateur = pr.user_id
                LEFT JOIN commentaire c ON u.id_utilisateur = c.auteur_id
                LEFT JOIN projet_reaction pjr ON u.id_utilisateur = pjr.user_id
                LEFT JOIN projet_commentaire pjc ON u.id_utilisateur = pjc.auteur_id
                GROUP BY u.id_utilisateur
            ) as user_activities
        ";
        $stmt = $this->conn->prepare($query_avg);
        $stmt->execute();
        $avg_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $avg_activity = $avg_result['avg_score'] ?? 5;

        // Avoid division by zero
        if ($avg_activity == 0) {
            $avg_activity = 1;
        }

        // Calculate score (capped at 100)
        $activity_score = min(100, ($total_activity / $avg_activity) * 100);
        return round($activity_score, 2);
    }

    /**
     * Get matched projects for a user (sorted by score)
     */
    public function getMatchedProjects($user_id, $limit = 10, $offset = 0) {
        // Get active projects
        $query = "
            SELECT p.*, 
                   COUNT(DISTINCT pr.id) as like_count,
                   COUNT(DISTINCT pc.id) as comment_count
            FROM projet p
            LEFT JOIN projet_reaction pr ON p.id = pr.projet_id
            LEFT JOIN projet_commentaire pc ON p.id = pc.projet_id
            WHERE p.statut = 'actif' 
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit, $offset]);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate match scores for each project
        foreach ($projects as &$project) {
            $project['match_score'] = $this->calculateMatchScore($user_id, $project['id']);
            
            // Get skill details for display
            $project['matched_skills'] = $this->getMatchedSkills($user_id, $project['id']);
            $project['required_skills'] = $this->getRequiredSkills($project['id']);
        }

        // Sort by match score descending
        usort($projects, function($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });

        return $projects;
    }

    /**
     * Get matching skills for display
     */
    public function getMatchedSkills($user_id, $projet_id) {
        $query = "
            SELECT DISTINCT s.id, s.name, s.category
            FROM skill s
            INNER JOIN user_skill us ON s.id = us.skill_id
            INNER JOIN project_skill ps ON s.id = ps.skill_id
            WHERE us.user_id = ? AND ps.projet_id = ?
            ORDER BY s.name
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $projet_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get required skills for project
     */
    public function getRequiredSkills($projet_id) {
        $query = "
            SELECT s.id, s.name, s.category, ps.required, ps.priority
            FROM skill s
            INNER JOIN project_skill ps ON s.id = ps.skill_id
            WHERE ps.projet_id = ? AND ps.required = 1
            ORDER BY ps.priority, s.name
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$projet_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Record user action (interested, skipped, applied)
     */
    public function recordAction($user_id, $projet_id, $action = 'interested') {
        $query = "INSERT INTO " . $this->action_table . " (user_id, projet_id, action) 
                  VALUES (?, ?, ?)
                  ON DUPLICATE KEY UPDATE action = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $projet_id, $action, $action]);
    }

    /**
     * Get user match actions
     */
    public function getUserActions($user_id, $action = null) {
        $query = "SELECT * FROM " . $this->action_table . " WHERE user_id = ?";
        if ($action) {
            $query .= " AND action = ?";
        }
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        if ($action) {
            $stmt->execute([$user_id, $action]);
        } else {
            $stmt->execute([$user_id]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Clear old cache entries
     */
    public function clearExpiredCache() {
        $query = "DELETE FROM " . $this->cache_table . " WHERE expires_at < NOW()";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }
}
?>
