-- the default option table for storing the version
CREATE TABLE opts (opt,val);
CREATE UNIQUE INDEX idx_opt ON opts(opt);
