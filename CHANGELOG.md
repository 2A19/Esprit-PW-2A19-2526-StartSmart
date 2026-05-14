# Changelog

All notable changes to StartSmart Forum application are documented here.

## [Unreleased]

### Added
- **User Profiles**: Full user profile system with biography, avatar, and city. User activity feeds showing recent posts and comments. Profile links on all post/comment author names.
  - Models: `User`
  - Controller: `UserController` (actions: profile, edit)
  - Database: Added `bio`, `avatar_url`, `city`, `created_at` columns to `utilisateur` table
  - Views: `views/user/profile.php`, `views/user/edit.php`
  - UI: Clickable author names linking to profiles in posts and comments

- **Notifications System (SSE)**: Real-time notifications with Server-Sent Events (SSE) stream, fallback polling, and persistent DB storage. Triggers on comments, replies, and reactions.
  - Models: `Notification`
  - Services: `NotificationService`
  - Controller: `NotificationController` (actions: count, list, markRead, markAllRead, stream)
  - UI: Dropdown with badge in navbar (`views/partials/notifications.php`)
  - Client: EventSource streaming + fallback polling in `forum.js`

- **Reaction System (Like/Dislike)**: Users can react (LIKE/DISLIKE) to posts and comments with toggle semantics.
  - Models: `Reaction`
  - Controller: `ReactionController` (actions: togglePost, toggleComment)
  - Database: Single `reaction` table with `type` column
  - UI: Like/Dislike buttons on posts and comments with counts

- **Threaded Comments**: Comments can have replies (parent_id relationship).
  - Model: `Commentaire` with nested structure support
  - Controller: `CommentaireController::addAsync()` for AJAX replies
  - UI: Nested comment rendering with reply buttons in `views/post/show.php`

- **Pagination**: Server-side pagination for posts listing.
  - Model: `Post::readAll()` with LIMIT/OFFSET
  - Model: `Post::countAll()` to support pagination UI
  - UI: Pagination controls in `views/post/index.php`

- **Search**: Filter posts by title/content.
  - Implementation: `Post::readAll($search, ...)`
  - UI: Search box in `views/post/index.php`

- **Auth & Authorization**:
  - Config: `config/Auth.php` with helpers (isLoggedIn, currentUserId, requireLogin, requireOwnershipOrAdmin)
  - Service: `AuthService` for login/register logic
  - Controllers: All endpoints require login; edit/delete requires ownership or admin role

- **Enhanced UI/UX**:
  - Empty state messages
  - Animations (pop effect on button clicks)
  - Responsive layout
  - Styled pagination and search controls
  - CSS: `forum.css`, `style.css`

### Changed
- Database: Auto-create `reaction` and `notification` tables in `Database::syncSchema()` on connection
- Controllers: Integrated notification creation in `CommentaireController` and `ReactionController`

## [v0.1.0] - 2026-05-06

### Initial Release
- Forum CRUD (posts, comments)
- User authentication (login/logout)
- Project & Category management
- Basic styling and navigation
