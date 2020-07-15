USE billing;
CREATE TABLE transction
(
  id              INT(20) unsigned NOT NULL AUTO_INCREMENT,
  target          VARCHAR(20) NOT NULL,  
  amount          INT(10) NOT NULL,
  sessionID       VARCHAR(255) NOT NULL,   
  timestamp       INT(20) NOT NULL,     
  completed       BOOLEAN NOT NULL DEFAULT 0,     
  PRIMARY KEY     (id)                               
);
