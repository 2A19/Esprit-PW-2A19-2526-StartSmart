<?php
require_once 'config/Auth.php';
require_once 'config/Database.php';
require_once 'models/Reaction.php';
require_once 'models/Post.php';
require_once 'models/Commentaire.php';
require_once 'services/NotificationService.php';

class ReactionController {
    private $db;
    private $reaction;

    public function __construct() {
        // Require login for AJAX requests
        if (!isLoggedIn()) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized", "success" => false]);
            exit;
        }

        $database = new Database();
        $this->db = $database->getConnection();
        $this->reaction = new Reaction($this->db);
        $this->notificationService = new NotificationService($this->db);
    }

    public function togglePost() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"));
            
            if (!empty($data->id_post)) {
                $this->reaction->post_id = $data->id_post;
                $this->reaction->user_id = currentUserId();
                $reactionType = !empty($data->reaction_type) ? strtoupper($data->reaction_type) : 'LIKE';
                
                $result = $this->reaction->togglePostReaction($reactionType);
                
                if ($result) {
                    $likesCount = $this->reaction->countPostReactionsByType((int) $data->id_post, 'LIKE');
                    $dislikesCount = $this->reaction->countPostReactionsByType((int) $data->id_post, 'DISLIKE');
                    // notify post author if someone else reacted
                    try {
                        $post = new Post($this->db);
                        $post->id_post = (int) $data->id_post;
                        if ($post->readOne()) {
                            $postAuthorId = (int) $post->auteur_id;
                            if ($postAuthorId && $postAuthorId !== currentUserId()) {
                                $this->notificationService->createNotification($postAuthorId, currentUserId(), 'REACTION_ON_POST', $post->id_post, 'Quelqu\u2019un a réagi à votre post');
                            }
                        }
                    } catch (Exception $e) {}
                    
                    http_response_code(200);
                    header('Content-Type: application/json');
                    echo json_encode([
                        "message" => "Reaction updated", 
                        "success" => true, 
                        "status" => $result['status'],
                        "current_type" => $result['current_type'] ?? null,
                        "likes_count" => $likesCount,
                        "dislikes_count" => $dislikesCount
                    ]);
                    exit;
                }
            }
            
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["message" => "Bad Request.", "success" => false]);
            exit;
        }
    }

    public function toggleComment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"));

            if (!empty($data->id_commentaire)) {
                $this->reaction->comment_id = (int) $data->id_commentaire;
                $this->reaction->user_id = currentUserId();
                $reactionType = !empty($data->reaction_type) ? strtoupper($data->reaction_type) : 'LIKE';

                $result = $this->reaction->toggleCommentReaction($reactionType);

                if ($result) {
                    $likesCount = $this->reaction->countCommentReactionsByType((int) $data->id_commentaire, 'LIKE');
                    $dislikesCount = $this->reaction->countCommentReactionsByType((int) $data->id_commentaire, 'DISLIKE');
                    // notify comment author
                    try {
                        $comment = new Commentaire($this->db);
                        $comment->id_commentaire = (int) $data->id_commentaire;
                        if ($comment->readOne()) {
                            $commentAuthorId = (int) $comment->auteur_id;
                            if ($commentAuthorId && $commentAuthorId !== currentUserId()) {
                                $this->notificationService->createNotification($commentAuthorId, currentUserId(), 'REACTION_ON_COMMENT', $comment->id_commentaire, 'Quelqu\u2019un a réagi à votre commentaire');
                            }
                        }
                    } catch (Exception $e) {}
                    http_response_code(200);
                    header('Content-Type: application/json');
                    echo json_encode([
                        "message" => "Comment reaction updated",
                        "success" => true,
                        "status" => $result['status'],
                        "current_type" => $result['current_type'] ?? null,
                        "likes_count" => $likesCount,
                        "dislikes_count" => $dislikesCount
                    ]);
                    exit;
                }
            }

            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["message" => "Bad Request.", "success" => false]);
            exit;
        }
    }
}
?>
