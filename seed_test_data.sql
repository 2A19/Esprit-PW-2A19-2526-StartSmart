-- Insert sample startups
INSERT INTO startups (user_id, nom_startup, nom_responsable, prenom_responsable, email, password, telephone, secteur, site_web, stade, statut, date_inscription) VALUES
(3, 'TechTunisia', 'Chaabane', 'Mehdi', 'contact@techtunisia.tn', '$2y$10$easGrqBneI/vciSwnWB/0.cSBqYGR.IT2kIgSVbhCajRQHB7qyhoa', '55001122', 'Technologie', 'https://techtunisia.tn', 'mvp', 'actif', '2026-05-12 10:27:21'),
(1, 'DesignHub', 'Ben Ali', 'Ahmed', 'ahmed@designhub.tn', '$2y$10$rTEyIxTgyqHlu66exgsNm.iOJ6C7T.pBJs2lie6yPei1ua71MP9qu', '55123456', 'Design & Creative', 'https://designhub.tn', 'prototype', 'actif', '2026-05-13 10:00:00');

-- Insert sample job offers
INSERT INTO job_offers (user_id, title, description, requirements, salary_min, salary_max, location, type, status) VALUES
(3, 'Senior Full Stack Developer', 'We are looking for an experienced full stack developer to join our growing team at TechTunisia. You will work on cutting-edge web applications using modern technologies.', 'PHP, MySQL, JavaScript, REST APIs, 5+ years experience', 1500.00, 2500.00, 'Tunis', 'Full-time', 'active'),
(3, 'DevOps Engineer', 'Help us scale our infrastructure. Looking for someone with Docker, Kubernetes, and AWS experience.', 'Docker, Kubernetes, AWS, Linux, CI/CD pipelines', 1800.00, 2800.00, 'Tunis', 'Full-time', 'active'),
(1, 'UI/UX Designer', 'Design beautiful and intuitive user interfaces for our digital products. Remote work available.', 'Figma, Adobe XD, Prototyping, User Research, 3+ years experience', 1200.00, 1800.00, 'Sfax', 'Full-time', 'active'),
(1, 'Junior Frontend Developer', 'Join our team as a junior frontend developer. We mentor and help you grow your skills.', 'React, Vue.js or Angular, HTML, CSS, JavaScript', 900.00, 1300.00, 'Sousse', 'Full-time', 'active');

-- Insert sample posts
INSERT INTO post (titre, topic, contenu, auteur_id, statut, likes_count, categorie_id) VALUES
('Best Practices for Startup Growth', 'Business', 'In this post, I want to share some proven strategies that helped us scale TechTunisia from an idea to MVP stage. First, focus on your core users and get feedback early. Second, iterate quickly based on feedback. Third, build a strong team culture...', 3, 'actif', 5, 1),
('Web Development Trends in 2026', 'Technology', 'The web development landscape is constantly evolving. Here are the top trends to watch out for this year: 1) AI-powered development tools, 2) Web Assembly gaining adoption, 3) Full-stack frameworks becoming more popular, 4) Serverless architecture...', 1, 'actif', 8, 1),
('How to Write Better Code', 'Technology', 'Code quality is crucial for long-term project success. In this guide, I cover: - Writing clean, readable code - Using design patterns effectively - Testing strategies - Code review best practices - Tools that help maintain code quality...', 2, 'actif', 12, 1),
('Design Thinking for Innovation', 'Business', 'Design thinking is a problem-solving methodology that can transform how your startup approaches challenges. It involves: empathy, ideation, prototyping, and testing. Let me walk through a real example from our recent project...', 1, 'actif', 6, 3);

-- Insert sample comments on posts
INSERT INTO commentaire (contenu, auteur_id, post_id) VALUES
('Great insights! I would add that customer feedback loops are critical. We made the mistake of building features without talking to users first.', 1, 1),
('This is exactly what we needed at our startup. Thanks for sharing!', 16, 1),
('Can you elaborate more on the MVP stage? What features did you prioritize?', 2, 1),
('100% agree on AI tools. We are already using them in our workflow and it''s a game changer.', 3, 2),
('Any recommendations for beginners? This seems overwhelming.', 16, 2),
('The testing part is so important! Too many developers skip this.', 1, 3),
('This should be required reading for all junior developers.', 2, 3),
('Love the design thinking approach. We used this in our last project and it worked wonders!', 3, 4),
('Do you have any specific tools you recommend for prototyping?', 16, 4);

-- Insert some project data
INSERT INTO projet (nomprojet, datedebut, datefin, budget, gain, categorie_id, auteur_id, statut) VALUES
('E-Commerce Platform', '2026-01-01', '2026-12-31', 50000.00, 0.00, 1, 3, 'actif'),
('Mobile Banking App', '2026-02-15', '2026-11-30', 75000.00, 0.00, 2, 3, 'actif'),
('Brand Redesign Campaign', '2026-03-01', '2026-06-30', 25000.00, 0.00, 3, 1, 'actif'),
('Cloud Migration Project', '2026-04-01', '2026-09-30', 100000.00, 0.00, 3, 3, 'actif');

-- Insert some project reactions
INSERT INTO projet_reaction (user_id, projet_id, type) VALUES
(1, 1, 'LIKE'),
(2, 1, 'LIKE'),
(16, 2, 'LIKE'),
(3, 3, 'LIKE'),
(1, 3, 'LIKE'),
(2, 4, 'LIKE');

-- Insert some project comments
INSERT INTO projet_commentaire (projet_id, auteur_id, contenu) VALUES
(1, 1, 'This project is progressing really well. The team is doing great work!'),
(1, 16, 'When can we expect the first beta release?'),
(2, 3, 'Mobile app security is our top priority. All payments are encrypted.'),
(3, 1, 'The new brand identity is beautiful and modern.'),
(4, 2, 'Cloud migration is complex but necessary for scalability.');

COMMIT;
