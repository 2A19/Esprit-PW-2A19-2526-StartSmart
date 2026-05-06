<?php
namespace Services;

require_once 'models/Competence.php';
require_once 'models/Projet.php';
require_once 'models/ProjetReaction.php';

use Competence;
use Projet;
use ProjetReaction;
use PDO;

class MatchService {
    private $db;
    private $competenceModel;
    private $projetModel;
    private $reactionModel;

    public function __construct($db) {
        $this->db = $db;
        $this->competenceModel = new Competence($db);
        $this->projetModel = new Projet($db);
        $this->reactionModel = new ProjetReaction($db);
    }

    public function calculateMatches($userId) {
        $userId = (int)$userId;

        // 1. Get user profile (Skills & Interests)
        $userCompetences = $this->competenceModel->getUserCompetences($userId);
        $userInterests = $this->competenceModel->getUserInterests($userId);
        
        $userCompIds = array_column($userCompetences, 'id');
        $userInterestIds = array_column($userInterests, 'id');

        // 2. Fetch Projects (excluding own projects and already swiped ones)
        // This is a highly optimized query replacing N+1 calls
        $query = "SELECT p.id, p.nomprojet, p.description, p.budget, p.categorie_id, 
                         c.typeprojet AS categorie_nom,
                         (SELECT COUNT(*) FROM projet_reaction pr WHERE pr.projet_id = p.id AND pr.type = 'LIKE') as likes_count,
                         (SELECT COUNT(*) FROM projet_commentaire pc WHERE pc.projet_id = p.id AND pc.statut = 'actif') as commentaires_count
                  FROM projet p
                  LEFT JOIN categorie c ON p.categorie_id = c.id
                  WHERE p.statut = 'actif' 
                  AND p.auteur_id != :user_id
                  AND p.id NOT IN (
                      SELECT projet_id FROM projet_reaction WHERE user_id = :user_id
                  )
                  ORDER BY p.created_at DESC LIMIT 100";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $projets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($projets)) {
            return [];
        }

        // 3. Fetch all required competences for these projects in ONE query (Solving N+1)
        $projetIds = array_column($projets, 'id');
        $placeholders = implode(',', array_fill(0, count($projetIds), '?'));
        
        $compQuery = "SELECT pc.projet_id, c.id as competence_id, c.nom 
                      FROM projet_competence pc 
                      JOIN competence c ON pc.competence_id = c.id 
                      WHERE pc.projet_id IN ($placeholders)";
        $compStmt = $this->db->prepare($compQuery);
        $compStmt->execute($projetIds);
        $allProjCompetences = $compStmt->fetchAll(PDO::FETCH_ASSOC);

        // Group competences by project
        $projetCompetencesMap = [];
        foreach ($allProjCompetences as $row) {
            $projetCompetencesMap[$row['projet_id']][] = [
                'id' => $row['competence_id'],
                'nom' => $row['nom']
            ];
        }

        // 4. Calculate Match Scores
        $matches = [];
        foreach ($projets as $projet) {
            $projetId = $projet['id'];
            $reqSkills = $projetCompetencesMap[$projetId] ?? [];
            $reqSkillIds = array_column($reqSkills, 'id');
            
            // --- A. SKILL SCORE (Max 50 points) ---
            $skillScore = 0;
            $matchingSkillIds = [];
            
            if (count($reqSkillIds) > 0) {
                // Percentage of required skills the user has
                $intersect = array_intersect($userCompIds, $reqSkillIds);
                $matchingSkillIds = $intersect;
                $skillScore = (count($intersect) / count($reqSkillIds)) * 50;
            } else {
                // If the project requires no specific skills, we shouldn't penalize.
                // We shift the weight dynamically: the max score is now based heavily on interests and activity.
                // To keep math simple, we grant 25/50 flat if they have matching interests, otherwise 10.
                if (in_array($projet['categorie_id'], $userInterestIds)) {
                    $skillScore = 30; // Bonus for interest overlap
                } else {
                    $skillScore = 15; // Neutral
                }
            }

            // --- B. INTEREST SCORE (Max 30 points) ---
            $interestScore = 0;
            if (in_array($projet['categorie_id'], $userInterestIds)) {
                $interestScore = 30;
            }

            // --- C. ACTIVITY/POPULARITY SCORE (Max 20 points) ---
            $likes = (int)($projet['likes_count'] ?? 0);
            $comments = (int)($projet['commentaires_count'] ?? 0);
            // Formula: 1 like = 2 pts, 1 comment = 4 pts. Max capped at 20.
            $activityScore = min(20, ($likes * 2) + ($comments * 4));

            // --- TOTAL SCORE ---
            $totalScore = round($skillScore + $interestScore + $activityScore);

            $projet['match_score'] = $totalScore;
            $projet['matching_skills'] = $matchingSkillIds;
            $projet['required_skills'] = $reqSkills;

            $matches[] = $projet;
        }

        // 5. Sort by Match Score Descending
        usort($matches, function($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });

        return $matches;
    }
}
?>
