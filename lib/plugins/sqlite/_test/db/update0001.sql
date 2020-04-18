CREATE TABLE testdata (tid INTEGER PRIMARY KEY, keyword, value);
CREATE INDEX idx_keyword ON testdata(keyword);
INSERT INTO testdata VALUES(1,'book','Nice reading');
INSERT INTO testdata VALUES(2,'book','Long reading');
INSERT INTO testdata VALUES(3,'music','happy');
INSERT INTO testdata VALUES(4,'music','Classic');
INSERT INTO testdata VALUES(5,'music','Pop');
INSERT INTO testdata VALUES(6,'glass','Black');
INSERT INTO testdata VALUES(7,'glass','Red');
INSERT INTO testdata VALUES(8,'music','Pink');
INSERT INTO testdata VALUES(9,'book','Black');
INSERT INTO testdata VALUES(10,'music','Boring');




