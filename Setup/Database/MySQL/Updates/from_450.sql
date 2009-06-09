-- contents as calendar events
CREATE TABLE IF NOT EXISTS 
EventDates(
	contentREL
		INTEGER
		NOT NULL,
	startDate 
		DATETIME 
		NOT NULL,
	endDate
		DATETIME 
		NOT NULL,
	fullDays
		ENUM('Y', 'N')
		DEFAULT 'N'
		NOT NULL,
	autoGenerated
		ENUM('Y', 'N')
		DEFAULT 'N'
		NOT NULL,
	INDEX(contentREL),
	INDEX(startDate, endDate)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

ALTER TABLE 
EventDates
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;
