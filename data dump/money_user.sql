CREATE TABLE `money_user` (
  `SYS_USER_ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_EMAIL` varchar(200) NOT NULL,
  `USER_PASSWORD` varchar(30) NOT NULL,
  `USER_TERMS_AGREE` tinyint(1) NOT NULL DEFAULT 1,
  `SYS_CREATE_DATE` timestamp NOT NULL DEFAULT current_timestamp(),
  `SYS_LAST_MODIFIED` timestamp NOT NULL DEFAULT current_timestamp(),
  `USER_FIRST_NAME` varchar(45) DEFAULT NULL,
  `USER_LAST_NAME` varchar(45) DEFAULT NULL,
  `USER_GENDER` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`SYS_USER_ID`),
  UNIQUE KEY `USERID` (`SYS_USER_ID`)
);

select * from money_account_type;
insert into money_account_type values(4,"Investment");