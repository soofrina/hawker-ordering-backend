-- ════════════════════════════════════════════════════════════════
-- Hawker ordering platform — schema from team's FINAL ERD
-- MySQL / filess.io.  Run ONCE in the filess.io web client:
--   File > New query  →  paste all of this  →  Run (Ctrl+Enter)
-- Tables are created parent-first so foreign keys resolve correctly.
-- ════════════════════════════════════════════════════════════════

CREATE TABLE `user` (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100),
    email         VARCHAR(100),
    phone_number  VARCHAR(20),
    password_hash VARCHAR(255),                       -- store a HASH, never the raw password
    role          VARCHAR(20),                        -- e.g. customer / hawker
    status        VARCHAR(20),
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE authentication (
    auth_id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT NOT NULL,
    two_factor_enabled BOOLEAN DEFAULT 0,
    verification_code  VARCHAR(10),
    code_expiry        DATETIME,
    last_login         DATETIME,
    FOREIGN KEY (user_id) REFERENCES `user`(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stall (
    stall_id       INT AUTO_INCREMENT PRIMARY KEY,
    stall_name     VARCHAR(100),
    location       VARCHAR(100),
    open_status    BOOLEAN DEFAULT 1,                 -- the manual open/closed override
    is_active      BOOLEAN DEFAULT 1,
    day_of_week    VARCHAR(10),
    open_time      TIME,
    close_time     TIME,
    stall_owner_id INT,                               -- the hawker (a user)
    FOREIGN KEY (stall_owner_id) REFERENCES `user`(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menu_item (
    item_id      INT AUTO_INCREMENT PRIMARY KEY,
    stall_id     INT NOT NULL,
    item_name    VARCHAR(100),
    description  VARCHAR(255),
    base_price   DECIMAL(10,2),
    image_url    VARCHAR(255),
    quantity     INT,
    availability BOOLEAN DEFAULT 1,                   -- the real-time sold-out toggle
    FOREIGN KEY (stall_id) REFERENCES stall(stall_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `order` (
    order_id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT NOT NULL,
    stall_id          INT NOT NULL,
    order_type        VARCHAR(20),                    -- dine-in / takeaway
    total_amount      DECIMAL(10,2),
    order_status      VARCHAR(20),                    -- received / preparing / ready ...
    queue_number      INT,
    verification_code VARCHAR(10),
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES `user`(user_id),
    FOREIGN KEY (stall_id) REFERENCES stall(stall_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_item (
    order_id_item   INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT NOT NULL,
    item_id         INT NOT NULL,
    quantity        INT,
    special_request VARCHAR(255),                     -- the per-item note
    item_price      DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES `order`(order_id),
    FOREIGN KEY (item_id)  REFERENCES menu_item(item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Optional seed data so you can test straight away ──
INSERT INTO `user` (name, role, status) VALUES ('Demo Hawker', 'hawker', 'active');
INSERT INTO stall (stall_name, location, stall_owner_id) VALUES ('Ah Hock Chicken Rice', 'Blk 123', 1);
INSERT INTO menu_item (stall_id, item_name, base_price, availability) VALUES
    (1, 'Roasted Chicken Rice', 4.50, 1),
    (1, 'Steamed Chicken Rice', 4.50, 1);
