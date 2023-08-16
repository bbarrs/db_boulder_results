-- create database
CREATE DATABASE boulder_results;

-- use the database
USE boulder_results;

-- create the tables
CREATE TABLE competition
(
  comp_id		INT		PRIMARY KEY		AUTO_INCREMENT,
  comp_name		VARCHAR(100)		NOT NULL,
  comp_city		VARCHAR(100),
  comp_country	VARCHAR(100),
  start_date	DATE,
  end_date		DATE
);

CREATE TABLE climber
(
  climber_id		INT		PRIMARY KEY		AUTO_INCREMENT,
  climber_first		VARCHAR(100)		NOT NULL,
  climber_last		VARCHAR(100)		NOT NULL,
  climber_nation	VARCHAR(100)
);

CREATE TABLE comp_entry
(
  entry_id		INT		PRIMARY KEY		AUTO_INCREMENT,
  climber_id	INT		NOT NULL,
  comp_id		INT		NOT NULL,
  start_number	INT,
  climber_rank	INT,
  FOREIGN KEY(climber_id) REFERENCES climber (climber_id),
  FOREIGN KEY(comp_id) REFERENCES competition (comp_id)
);

CREATE TABLE results
(
	entry_id	INT NOT NULL AUTO_INCREMENT,
	level 		ENUM('q', 's', 'f') NOT NULL,
	tops 		INT,
	top_attempts INT,
	zones INT,
	zone_attempts INT,
	PRIMARY KEY (entry_id, level),
	FOREIGN KEY(entry_id) REFERENCES comp_entry (entry_id)
);

SET SQL_SAFE_UPDATES = 0;

-- delete one competition that has no start number
DELETE FROM results_csv
WHERE StartNr = '';


-- populate the climber table
INSERT INTO climber (climber_first, climber_last, climber_nation)
SELECT DISTINCT FIRST, LAST, Nation FROM results_csv
ORDER BY Nation, Last, First;

-- add climber_ids back from climber table to csv table
ALTER TABLE results_csv ADD climber_id INT;
ALTER TABLE results_csv ADD CONSTRAINT csv_climberid
FOREIGN KEY (climber_id) REFERENCES climber (climber_id);

UPDATE results_csv r JOIN climber c
ON r.FIRST = c.climber_first
AND r.LAST = c.climber_last
AND r.Nation = c.climber_nation
SET r.climber_id = c.climber_id;


-- decompose Competition Title
-- add columns to csv table to help with decomposition
ALTER TABLE results_csv
ADD comp_name VARCHAR(255),
ADD comp_city VARCHAR(255),
ADD comp_country VARCHAR(255);
UPDATE results_csv SET comp_name = `Competition Title`;

-- Delete year
UPDATE results_csv
SET comp_name = SUBSTRING(`Competition Title`, 1, LENGTH(`Competition Title`) - 5);

-- Handle country code
UPDATE results_csv
SET comp_country = SUBSTRING_INDEX(SUBSTRING_INDEX(comp_name, '(', -1), ')', 1),
    comp_name = SUBSTRING(comp_name, 1, LENGTH(comp_name) - 6);

-- Handle city
UPDATE results_csv
SET comp_city = SUBSTRING_INDEX(comp_name,' ', -1),
    comp_name = TRIM(SUBSTRING_INDEX(comp_name, '-', 1));
    
    
-- decompose Competition Date
-- add start and end columns
ALTER TABLE results_csv
ADD start_date DATE,
ADD end_date DATE;

-- start date
UPDATE results_csv
SET start_date = STR_TO_DATE(CONCAT(SUBSTRING_INDEX(`Competition Date`, ' ', 1), ' ',
						SUBSTRING_INDEX(`Competition Date`, ' ', -2)), '%e %M %Y')
WHERE LENGTH(TRIM(SUBSTRING_INDEX(`Competition Date`, ' ', 2))) <= 2;

-- special case (competition over two months)
UPDATE results_csv
SET start_date = STR_TO_DATE(CONCAT(SUBSTRING_INDEX(`Competition Date`, ' ', 2), ' ', 
						SUBSTRING_INDEX(`Competition Date`, ' ', -1)), '%e %M %Y')
WHERE LENGTH(TRIM(SUBSTRING_INDEX(`Competition Date`, ' ', 2))) > 2;

-- end date
UPDATE results_csv
SET end_date = STR_TO_DATE(TRIM(SUBSTRING_INDEX(`Competition Date`, '-', -1)), '%e %M %Y');

-- populate the competition table
INSERT INTO competition (comp_name, comp_city, comp_country, start_date, end_date)
SELECT DISTINCT comp_name, comp_city, comp_country, start_date, end_date
FROM results_csv
ORDER BY comp_name, end_date, start_date;

-- add comp_ids back from competition table to csv table
ALTER TABLE results_csv ADD comp_id INT;
ALTER TABLE results_csv ADD CONSTRAINT csv_compid
FOREIGN KEY (comp_id) REFERENCES competition (comp_id);

UPDATE results_csv r JOIN competition c
ON r.comp_name = c.comp_name
AND r.comp_city = c.comp_city
AND r.comp_country = c.comp_country
AND r.start_date = c.start_date
AND r.end_date = c.end_date
SET r.comp_id = c.comp_id;

-- populate the comp_entry table
INSERT INTO comp_entry (climber_id, comp_id, start_number, climber_rank)
SELECT DISTINCT climber_id, comp_id, StartNr, `Rank`
FROM results_csv
ORDER BY comp_id, `Rank`;

-- add entry_ids back into csv table
ALTER TABLE results_csv ADD entry_id INT;
ALTER TABLE results_csv ADD CONSTRAINT csv_entryid
FOREIGN KEY (entry_id) REFERENCES comp_entry (entry_id);

UPDATE results_csv r JOIN comp_entry c
ON r.climber_id = c.climber_id
AND r.comp_id = c.comp_id
AND r.StartNr = c.start_number
AND r.`Rank` = c.climber_rank
SET r.entry_id = c.entry_id;

-- qual population
INSERT INTO results (entry_id, level, tops, top_attempts, zones, zone_attempts)
SELECT DISTINCT entry_id, "q", Qtops, Qt_atts, Qzones, Qz_atts FROM results_csv
ORDER BY entry_id;

-- semi population
INSERT INTO results (entry_id, level, tops, top_attempts, zones, zone_attempts)
SELECT DISTINCT entry_id, "s", Stops, St_atts, Szones, Sz_atts FROM results_csv
ORDER BY entry_id;

-- final population
INSERT INTO results (entry_id, level, tops, top_attempts, zones, zone_attempts)
SELECT DISTINCT entry_id, "f", Ftops, Ft_atts, Fzones, Fz_atts FROM results_csv
ORDER BY entry_id;

-- drop csv table to remove redundancies
DROP TABLE results_csv;