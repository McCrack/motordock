
CREATE TABLE IF NOT EXISTS cb_cache(
	src_id BIGINT UNSIGNED NOT NULL,
	itm_id BIGINT UNSIGNED NOT NULL,
	PRIMARY KEY(src_id, itm_id)
)ENGINE=InnoDB CHARACTER SET utf8;

/* Users ***************************/

CREATE TABLE IF NOT EXISTS users(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(191) NOT NULL,
	email VARCHAR(191) NOT NULL,
	password VARCHAR(191) NOT NULL,
	group ENUM('guest','admin','developer','manager','partner') NOT NULL DEFAULT 'guest',
	remember_token VARCHAR(100),
	created_at TIMESTAMP,
	updated_at TIMESTAMP,
	token BLOB,
	PRIMARY KEY(id),
	UNIQUE users_email_unique(email)
)ENGINE=InnoDB CHARACTER SET utf8;

/* Categories **********************/

CREATE TABLE IF NOT EXISTS cb_categories(
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`parent_id` INT UNSIGNED NOT NULL,
	`left_key` INT UNSIGNED,
	`right_key` INT UNSIGNED,
	`branch_id` INT UNSIGNED,
	`level` SMALLINT UNSIGNED NOT NULL,
	`EBAY-GB` INT UNSIGNED NOT NULL,
	`EBAY-DE` INT UNSIGNED NOT NULL,
	`name` JSON,
	`slug` VARCHAR(128) NOT NULL,
	`status` ENUM('enabled','disabled') NOT NULL DEFAULT 'enabled',
	`delivery_price` INT(4) UNSIGNED NOT NULL DEFAULT 40,
	`favorite` INT(1) UNSIGNED,
	PRIMARY KEY(`id`),
	UNIQUE(`EBAY-GB`),
	INDEX favorites(`favorite`)
)ENGINE=InnoDB CHARACTER SET utf8;

INSERT INTO gb_categories (
	`parent_id`,
	`level`,
	`EBAY-GB`,
	`EBAY-DE`,
	`name`,
	`slug`,
	`status`,
	`delivery_price`,
	`favorite`
) (
SELECT 
	`ParentID`,
	`Level`,
	`CatID`,
	`EBAY-DE`,
	`name`,
	`slug`,
	`status`,
	`delivery_price`,
	`favorite`
FROM cb_categories
ORDER BY Level)


DROP FUNCTION IF EXISTS rebuild_nested_set_tree;

DELIMITER $$
CREATE FUNCTION rebuild_nested_set_tree()
RETURNS INT DETERMINISTIC MODIFIES SQL DATA
BEGIN
    -- Изначально сбрасываем все границы в NULL
    UPDATE cb_categories t SET left_key = NULL, right_key = NULL;
    
    -- Устанавливаем границы корневым элементам
    SET @i := 0;
    UPDATE cb_categories t SET left_key = (@i := @i + 1), right_key = (@i := @i + 1)
    WHERE t.parent_id = 0;

    forever: LOOP
        -- Находим элемент с минимальной правой границей -- самый левый в дереве
        SET @parent_id := NULL;
        SELECT t.id, t.right_key FROM cb_categories t, cb_categories tc
        WHERE t.id = tc.parent_id AND tc.left_key IS NULL AND t.right_key IS NOT NULL
        ORDER BY t.right_key LIMIT 1 INTO @parent_id, @parent_right;

        -- Выходим из бесконечности, когда у нас уже нет незаполненных элементов
        IF @parent_id IS NULL THEN LEAVE forever; END IF;

        -- Сохраняем левую границу текущего ряда
        SET @current_left := @parent_right;

        -- Вычисляем максимальную правую границу текущего ряда
        SELECT @current_left + COUNT(*) * 2 FROM cb_categories
        WHERE parent_id = @parent_id INTO @parent_right;

        -- Вычисляем длину текущего ряда
        SET @current_length := @parent_right - @current_left;

        -- Обновляем правые границы всех элементов, которые правее
        UPDATE cb_categories t SET right_key = right_key + @current_length
        WHERE right_key >= @current_left ORDER BY right_key;

        -- Обновляем левые границы всех элементов, которые правее
        UPDATE cb_categories t SET left_key = left_key + @current_length
        WHERE left_key > @current_left ORDER BY left_key;

        -- И только сейчас обновляем границы текущего ряда
        SET @i := (@current_left - 1);
        UPDATE cb_categories t SET left_key = (@i := @i + 1), right_key = (@i := @i + 1)
        WHERE parent_id = @parent_id ORDER BY id;
    END LOOP;

    -- Возвращаем самый самую правую границу для дальнейшего использования
    RETURN (SELECT MAX(right_key) FROM cb_categories t);
END$$

DELIMITER ;

-- Выбор дочерних узлов
SELECT * FROM cb_categories WHERE left_key >= $left_key AND right_key <= $right_key ORDER BY left_key
-- Выбор всех родительских узлов
SELECT * FROM cb_categories WHERE left_key <= $left_key AND right_key >= $right_key ORDER BY left_key
-- Выбор ветки, в которой учавствует узел
SELECT * FROM cb_categories WHERE right_key > $left_key AND left_key < $right_key ORDER BY left_key

/* Store ***************************/

CREATE TABLE IF NOT EXISTS cb_sellers(
	SellerID INT UNSIGNED NOT NULL AUTO_INCREMENT,
	SellerName VARCHAR(32) NOT NULL DEFAULT '',
	StoreName VARCHAR(32) NOT NULL DEFAULT '',
	alias VARCHAR(32),
	market ENUM('EBAY-ENCA','EBAY-GB','EBAY-AT','EBAY-FR','EBAY-DE','EBAY-IT','EBAY-NL','EBAY-ES','EBAY-CH','EBAY-HK','EBAY-IE','EBAY-PL'),
	PRIMARY KEY(SellerID)
)ENGINE=InnoDB CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS cb_store(
	ThingID INT UNSIGNED NOT NULL,
	CategoryID INT UNSIGNED NOT NULL,
	BrandID INT UNSIGNED,
	DiscountID INT UNSIGNED,
	status ENUM('available','not available','new','sold','deleted') NOT NULL DEFAULT 'new',
	named JSON,
	preview VARCHAR(256),
	selling DECIMAL(8,2) NOT NULL DEFAULT 0.00,
	line_id INT UNSIGNED,
	motor_id VARCHAR(32),
	PRIMARY KEY(ThingID),
	FOREIGN KEY (ThingID) REFERENCES cb_things(ThingID)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	FOREIGN KEY (CategoryID) REFERENCES cb_categories(CatID)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	FOREIGN KEY (BrandID) REFERENCES cb_brands(BrandID)
		ON UPDATE CASCADE
		ON DELETE SET NULL,
	FOREIGN KEY (DiscountID) REFERENCES cb_discounts(DiscountID)
		ON UPDATE CASCADE
		ON DELETE SET NULL
	FOREIGN KEY (line_id) REFERENCES cb_lineups(line_id)
		ON UPDATE CASCADE
		ON DELETE SET NULL
	FOREIGN KEY (motor_id) REFERENCES cb_motors(motor_id)
		ON UPDATE CASCADE
		ON DELETE SET NULL
)ENGINE=InnoDB CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS cb_extended(
	ThingID INT UNSIGNED NOT NULL,
	SellerID INT UNSIGNED NOT NULL,
	ReferenceID VARCHAR(16),
	eBayID BIGINT UNSIGNED,
	DescriptionID INT UNSIGNED,
	purchase DECIMAL(8,2) NOT NULL DEFAULT 0.00,
	currency ENUM('EUR','USD','GBP','UAH') NOT NULL DEFAULT 'GBP',
	options JSON,
	images JSON,
	compatibility JSON,	
	PRIMARY KEY(ThingID),
	UNIQUE(eBayID),
	UNIQUE(SellerID, ReferenceID),
	FOREIGN KEY (ThingID) REFERENCES cb_things(ThingID)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	FOREIGN KEY (SellerID) REFERENCES cb_sellers(SellerID)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	FOREIGN KEY (DescriptionID) REFERENCES cb_materials(ThingID)
		ON UPDATE CASCADE
		ON DELETE SET NULL
)ENGINE=InnoDB CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS cb_wordlist(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	en VARCHAR(48),
	de VARCHAR(48),
	ru VARCHAR(48),
	PRIMARY KEY(id)
)ENGINE=InnoDB CHARACTER SET utf8;

/* Brands **************************/

CREATE TABLE IF NOT EXISTS cb_brands(
	BrandID INT UNSIGNED NOT NULL AUTO_INCREMENT,
	idx CHAR(1) NOT NULL DEFAULT '',
	brand VARCHAR(32) NOT NULL DEFAULT '',
	regular VARCHAR(64) NOT NULL DEFAULT '',
	slug VARCHAR(32) NOT NULL DEFAULT '',
	logo VARCHAR(256),
	favorite INT(1) UNSIGNED NOT NULL DEFAULT 0,
	available INT(1) UNSIGNED DEFAULT 1,
	PRIMARY KEY(BrandID),
	UNIQUE(slug),
	INDEX(idx)
)ENGINE=InnoDB CHARACTER SET utf8;

/* LINEUPS *************************/

CREATE TABLE IF NOT EXISTS cb_lineups(
	line_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	BrandID INT UNSIGNED NOT NULL,
	model VARCHAR(32) NOT NULL DEFAULT '',
	modifications VARCHAR(32),
	regular VARCHAR(32) NOT NULL DEFAULT '',
	slug VARCHAR(32) NOT NULL DEFAULT '',
	image VARCHAR(128) NOT NULL DEFAULT '/images/lineups/default.png',
	available  INT(1) UNSIGNED DEFAULT 0,
	PRIMARY KEY(line_id),
	UNIQUE(BrandID, slug),
	FOREIGN KEY (BrandID) REFERENCES cb_brands(BrandID)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)ENGINE=InnoDB CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS cb_errors(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	idx INT(1) UNSIGNED NOT NULL,
	ThingID INT UNSIGNED NOT NULL,
	status VARCHAR(256) NOT NULL DEFAULT '',
	PRIMARY KEY(id),
	INDEX(idx),
	FOREIGN KEY (ThingID) REFERENCES cb_things(ThingID)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)ENGINE=InnoDB CHARACTER SET utf8;

/* MOTORS **************************/

CREATE TABLE IF NOT EXISTS cb_motors(
	motor_id VARCHAR(32) NOT NULL,
	article VARCHAR(32) NOT NULL,
	picture VARCHAR(128) NOT NULL DEFAULT '/img/motors/default.png',
	specifications JSON,
	compatibility JSON,
	description TEXT,
	source VARCHAR(128),
	fullness ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'low',
	published INT(1) UNSIGNED,
	fuel VARCHAR(16),
	power VARCHAR(24),
	torque VARCHAR(24),
	capacity VARCHAR(16),
	PRIMARY KEY(motor_id)
)ENGINE=InnoDB CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS motors_vs_brands(
	BrandID INT UNSIGNED NOT NULL,
	motor_id VARCHAR(32) NOT NULL,
	UNIQUE(BrandID, motor_id),
	FOREIGN KEY (motor_id) REFERENCES cb_motors(motor_id)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	FOREIGN KEY (BrandID) REFERENCES cb_brands(BrandID)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)ENGINE=InnoDB CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS motors_vs_lineups(
	line_id INT UNSIGNED NOT NULL,
	motor_id VARCHAR(32) NOT NULL,
	UNIQUE(line_id, motor_id),
	FOREIGN KEY (motor_id) REFERENCES cb_motors(motor_id)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	FOREIGN KEY (line_id) REFERENCES cb_lineups(line_id)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)ENGINE=InnoDB CHARACTER SET utf8;

/* Storekeeper *********************/

CREATE TABLE IF NOT EXISTS cb_storekeeper(
	timestamp INT UNSIGNED NOT NULL,
	time TIMESTAMP,
	tag VARCHAR(16),
	task VARCHAR(32),
	affected INT(4) UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY(timestamp),
	INDEX(tag)
)ENGINE=InnoDB CHARACTER SET utf8;