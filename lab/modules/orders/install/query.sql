CREATE TABLE IF NOT EXISTS cb_orders(
	OrderID INT UNSIGNED NOT NULL AUTO_INCREMENT,
	order_number INT(5) UNSIGNED NOT NULL DEFAULT 1,
	CommunityID INT UNSIGNED,
	created_at INT UNSIGNED NOT NULL DEFAULT 1,
	updated_at INT UNSIGNED NOT NULL DEFAULT 1,
	price DECIMAL(8,2) NOT NULL DEFAULT '0.00',
	status SET('new','accepted','shipped','canceled') NOT NULL DEFAULT 'new',
	delivery JSON,
	message VARCHAR(1024),
	signature INT(1) NOT NULL DEFAULT 0,
	PRIMARY KEY(OrderID),
	FOREIGN KEY (CommunityID) REFERENCES cb_community(CommunityID)
		ON UPDATE CASCADE
		ON DELETE SET NULL
)ENGINE=InnoDB CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS orders_vs_store(
	OrderID INT UNSIGNED NOT NULL,
	ThingID INT UNSIGNED,
	amount INT(2) UNSIGNED NOT NULL DEFAULT 1,
	FOREIGN KEY (OrderID) REFERENCES cb_orders(OrderID)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	FOREIGN KEY (ThingID) REFERENCES cb_store(ThingID)
		ON UPDATE CASCADE
		ON DELETE SET NULL
)ENGINE=InnoDB CHARACTER SET utf8;