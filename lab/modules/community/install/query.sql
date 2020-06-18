CREATE TABLE IF NOT EXISTS cb_community(
	CommunityID INT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(32) NOT NULL DEFAULT 'n/a',
	last_name VARCHAR(32) NOT NULL DEFAULT 'n/a',
	phone VARCHAR(24) NOT NULL DEFAULT 'n/a',
	email VARCHAR(32) NOT NULL DEFAULT 'n/a',
	reputation INT(4) DEFAULT 1,
	PRIMARY KEY(CommunityID),
	UNIQUE(phone)
)ENGINE=InnoDB CHARACTER SET utf8;

INSERT INTO gb_community (Name, Email, Phone) VALUES ('Viktor Sibibel', 'v.sibibel@gmail.com', 965216303)