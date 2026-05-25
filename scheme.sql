CREATE TABLE admins (
    ID smallint NOT NULL AUTO_INCREMENT,
    name varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    email varchar(80) NOT NULL,
    pswd varchar(255) DEFAULT NULL,
    created date NOT NULL,
    role tinyint UNSIGNED NOT NULL DEFAULT 3,
    token varchar(60) NOT NULL,
    nonce varchar(60) NOT NULL,
    status tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY uniq_admins_email (email),
    PRIMARY KEY (ID)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO admins (
    name, email, pswd, created, role, token, nonce, status
)
VALUES (
    'Administrator do Sistema',
    'admin@localhost.ext',
    '$2y$10$5.wF.IvO0g6pfRFjFSkfeOP7U.iC1ImK9WB/v.xnxgznrU31Xffue', 
    CURDATE(),
    1,
    '6NO-1hRsF~cm3XJIvQEsokBSetmf.-DPxLo6fvV-km',
    'sO45iVPIY1',
    1
);
-- Senha 'Admin123'


CREATE TABLE categories (
    ID mediumint NOT NULL AUTO_INCREMENT,
    type varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    name varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    parent mediumint NOT NULL DEFAULT 0,
    slug varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    segment varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    content mediumtext,
    created date NOT NULL,
    KEY idx_categories_slug (slug),
    KEY idx_categories_parent (parent),
    UNIQUE KEY uniq_categories_segment (segment),
    PRIMARY KEY (ID)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE comments (
    ID int NOT NULL AUTO_INCREMENT, 
    type varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    related_id int NOT NULL, 
    email varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    parent int DEFAULT 0,
    content text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    approved tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
    KEY idx_comments_related (type, related_id, approved, created),
    KEY idx_comments_parent (parent),
    PRIMARY KEY (ID)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE medias (
    ID int NOT NULL AUTO_INCREMENT,
    related_type varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    related_id int NOT NULL,
    related_title varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    attachment JSON NOT NULL,
    created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_medias_created (created),
    KEY idx_medias_related (related_type, related_id),
    PRIMARY KEY (ID)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE menus (
    ID smallint unsigned NOT NULL AUTO_INCREMENT,
    name varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    parent smallint DEFAULT NULL,
    type varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    related_id mediumint DEFAULT NULL,
    label varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    url text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    sort smallint DEFAULT 0,
    KEY idx_menus_parent (parent),
    PRIMARY KEY (ID)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE pages (
    ID int NOT NULL AUTO_INCREMENT,
    title varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    content longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    summary varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    parent int DEFAULT 0,
    lastmod datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    slug varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    segment varchar(240) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    template varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'page.php',
    status tinyint UNSIGNED NOT NULL DEFAULT 0,
    KEY idx_pages_slug (slug),
    KEY idx_pages_status (status),
    KEY idx_pages_parent (parent),
    UNIQUE KEY uniq_pages_segment (segment),
    PRIMARY KEY (ID)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE posts (
    ID int NOT NULL AUTO_INCREMENT,
    title varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    author varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    content longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    summary varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated datetime DEFAULT NULL,
    slug varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    segment varchar(240) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    status tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
    FULLTEXT KEY idx_posts_fulltext_title_summary (title, summary),
    KEY idx_posts_slug (slug),
    KEY idx_posts_status_created (status, created),
    KEY idx_posts_status_updated (status, updated),
    UNIQUE KEY uniq_posts_segment (segment),
    PRIMARY KEY (ID)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE relations (
    ID bigint NOT NULL AUTO_INCREMENT,
    type varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    type_id int NOT NULL,
    category_id int NOT NULL,
    KEY idx_relations_type (type, type_id),
    KEY idx_relations_category (category_id),
    PRIMARY KEY (ID)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE statistics (
    ID bigint NOT NULL AUTO_INCREMENT,  
    title varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    URL varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    created date NOT NULL,
    period time NOT NULL,
    IP varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,   
    PRIMARY KEY (ID),
    KEY idx_statistics_created (created) 
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE users (
    ID mediumint NOT NULL AUTO_INCREMENT,
    name varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    username varchar(40) NOT NULL,
    email varchar(60) NOT NULL,
    pswd varchar(255) DEFAULT NULL,
    created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated datetime DEFAULT NULL,
    content text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    token varchar(60) NOT NULL,
    nonce  varchar(60) NOT NULL,
    status tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
    approved tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
    KEY idx_users_status (status),
    UNIQUE KEY uniq_users_username (username),
    UNIQUE KEY uniq_users_email (email),
    PRIMARY KEY (ID)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;