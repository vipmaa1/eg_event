-- Egypt Event Discovery Platform - Complete Database Schema
-- Generated for Laravel-based MVP

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    name VARCHAR(255) NOT NULL,
    profile_photo VARCHAR(500) NULL,
    bio TEXT NULL,
    email_verified_at TIMESTAMP NULL,
    phone_verified_at TIMESTAMP NULL,
    status ENUM('active','suspended','deleted') NOT NULL DEFAULT 'active',
    preferences JSON NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_users_email_status_created_at (email, status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX idx_sessions_user_id (user_id),
    CONSTRAINT fk_sessions_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token CHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_tokenable_type_id (tokenable_type, tokenable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    role ENUM('attendee','organizer','venue_owner','admin') NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_user_roles_user_role (user_id, role),
    INDEX idx_user_roles_user_role_active (user_id, role, is_active),
    CONSTRAINT fk_user_roles_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE organizers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    name_ar VARCHAR(255) NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    bio_en TEXT NULL,
    bio_ar TEXT NULL,
    logo VARCHAR(500) NULL,
    cover_image VARCHAR(500) NULL,
    website VARCHAR(500) NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    social_media JSON NULL,
    verified TINYINT(1) NOT NULL DEFAULT 0,
    verification_date TIMESTAMP NULL,
    status ENUM('active','suspended','pending') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_organizers_user_status (user_id, status),
    INDEX idx_organizers_slug_verified (slug, verified),
    CONSTRAINT fk_organizers_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE venues (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    name_ar VARCHAR(255) NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description_en TEXT NULL,
    description_ar TEXT NULL,
    city VARCHAR(255) NOT NULL,
    district VARCHAR(255) NULL,
    address_en VARCHAR(255) NULL,
    address_ar VARCHAR(255) NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    capacity INT NULL,
    cover_image VARCHAR(500) NULL,
    gallery JSON NULL,
    amenities JSON NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    verified TINYINT(1) NOT NULL DEFAULT 0,
    verification_date TIMESTAMP NULL,
    status ENUM('active','suspended','pending') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_venues_user_status (user_id, status),
    INDEX idx_venues_city_verified (city, verified),
    INDEX idx_venues_slug (slug),
    CONSTRAINT fk_venues_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organizer_id BIGINT UNSIGNED NOT NULL,
    venue_id BIGINT UNSIGNED NULL,
    created_by_user_id BIGINT UNSIGNED NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    title_ar VARCHAR(255) NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description_en TEXT NOT NULL,
    description_ar TEXT NULL,
    start_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP NOT NULL,
    category ENUM('concerts','sports','business','cultural','food','arts','family','nightlife','other') NOT NULL,
    cover_image VARCHAR(500) NULL,
    gallery JSON NULL,
    tags JSON NULL,
    status ENUM('draft','published','cancelled','completed') NOT NULL DEFAULT 'draft',
    has_tickets TINYINT(1) NOT NULL DEFAULT 0,
    is_free TINYINT(1) NOT NULL DEFAULT 0,
    view_count INT NOT NULL DEFAULT 0,
    attending_count INT NOT NULL DEFAULT 0,
    save_count INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_events_organizer_status (organizer_id, status),
    INDEX idx_events_venue_start_date (venue_id, start_date),
    INDEX idx_events_category_status_start (category, status, start_date),
    INDEX idx_events_status_start (status, start_date),
    INDEX idx_events_slug (slug),
    FULLTEXT INDEX ft_events_titles_descriptions (title_en, title_ar, description_en, description_ar),
    CONSTRAINT fk_events_organizer_id FOREIGN KEY (organizer_id) REFERENCES organizers(id) ON DELETE CASCADE,
    CONSTRAINT fk_events_venue_id FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE SET NULL,
    CONSTRAINT fk_events_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ticket_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id BIGINT UNSIGNED NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    name_ar VARCHAR(255) NULL,
    description_en TEXT NULL,
    description_ar TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'EGP',
    quantity_total INT NOT NULL,
    quantity_sold INT NOT NULL DEFAULT 0,
    sales_start TIMESTAMP NULL,
    sales_end TIMESTAMP NULL,
    max_per_order INT NOT NULL DEFAULT 10,
    min_per_order INT NOT NULL DEFAULT 1,
    status ENUM('available','sold_out','inactive') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_ticket_types_event_status (event_id, status),
    CONSTRAINT fk_ticket_types_event_id FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(255) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    event_id BIGINT UNSIGNED NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    quantity INT NOT NULL,
    subtotal_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    fees_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'EGP',
    status ENUM('pending','paid','cancelled','refunded') NOT NULL DEFAULT 'pending',
    payment_method ENUM('paymob','fawry','cash') NULL,
    payment_transaction_id VARCHAR(255) NULL,
    payment_details JSON NULL,
    paid_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_orders_user_status (user_id, status),
    INDEX idx_orders_event_status (event_id, status),
    INDEX idx_orders_order_number (order_number),
    INDEX idx_orders_status_created_at (status, created_at),
    CONSTRAINT fk_orders_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_orders_event_id FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_code VARCHAR(255) NOT NULL UNIQUE,
    order_id BIGINT UNSIGNED NOT NULL,
    event_id BIGINT UNSIGNED NOT NULL,
    ticket_type_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    price_paid DECIMAL(10,2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'EGP',
    holder_name VARCHAR(255) NOT NULL,
    holder_email VARCHAR(255) NOT NULL,
    holder_phone VARCHAR(20) NULL,
    qr_code_url VARCHAR(500) NULL,
    status ENUM('valid','used','cancelled','refunded') NOT NULL DEFAULT 'valid',
    checked_in TINYINT(1) NOT NULL DEFAULT 0,
    checked_in_at TIMESTAMP NULL,
    checked_in_by BIGINT UNSIGNED NULL,
    staff_notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_tickets_order_id (order_id),
    INDEX idx_tickets_event_status (event_id, status),
    INDEX idx_tickets_user_status (user_id, status),
    INDEX idx_tickets_ticket_code (ticket_code),
    INDEX idx_tickets_checked_in_event (checked_in, event_id),
    CONSTRAINT fk_tickets_order_id FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_tickets_event_id FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_tickets_ticket_type_id FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id) ON DELETE CASCADE,
    CONSTRAINT fk_tickets_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_tickets_checked_in_by FOREIGN KEY (checked_in_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE promo_codes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(255) NOT NULL UNIQUE,
    event_id BIGINT UNSIGNED NULL,
    organizer_id BIGINT UNSIGNED NULL,
    discount_type ENUM('percentage','fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    usage_limit INT NULL,
    usage_count INT NOT NULL DEFAULT 0,
    per_user_limit INT NOT NULL DEFAULT 1,
    min_order_amount DECIMAL(10,2) NULL,
    valid_from TIMESTAMP NOT NULL,
    valid_until TIMESTAMP NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_promo_codes_code_active (code, is_active),
    INDEX idx_promo_codes_event_active (event_id, is_active),
    CONSTRAINT fk_promo_codes_event_id FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_promo_codes_organizer_id FOREIGN KEY (organizer_id) REFERENCES organizers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    event_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    content TEXT NOT NULL,
    is_approved TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_comments_event_approved_created (event_id, is_approved, created_at),
    INDEX idx_comments_user_id (user_id),
    CONSTRAINT fk_comments_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_comments_event_id FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_comments_parent_id FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE follows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    organizer_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_follows_user_organizer (user_id, organizer_id),
    INDEX idx_follows_organizer_id (organizer_id),
    CONSTRAINT fk_follows_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_follows_organizer_id FOREIGN KEY (organizer_id) REFERENCES organizers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_event_actions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    event_id BIGINT UNSIGNED NOT NULL,
    action_type ENUM('attending','interested','saved') NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_user_event_actions (user_id, event_id, action_type),
    INDEX idx_user_event_actions_event_action (event_id, action_type),
    CONSTRAINT fk_user_event_actions_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_event_actions_event_id FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payouts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organizer_id BIGINT UNSIGNED NOT NULL,
    event_id BIGINT UNSIGNED NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'EGP',
    platform_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    net_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','processing','paid','failed') NOT NULL DEFAULT 'pending',
    method ENUM('bank_transfer','wallet','cash') NOT NULL,
    payment_details JSON NULL,
    transaction_id VARCHAR(255) NULL,
    paid_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_payouts_organizer_status (organizer_id, status),
    INDEX idx_payouts_event_id (event_id),
    CONSTRAINT fk_payouts_organizer_id FOREIGN KEY (organizer_id) REFERENCES organizers(id) ON DELETE CASCADE,
    CONSTRAINT fk_payouts_event_id FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX idx_jobs_queue (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache_locks (
    `key` VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
