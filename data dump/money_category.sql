CREATE TABLE `money_category` (
  `SYS_ACCOUNT_CATEGORY_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ACCOUNT_CATEGORY_NAME` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`SYS_ACCOUNT_CATEGORY_ID`),
  UNIQUE KEY `SYS_ACCOUNT_CATEGORY_ID_UNIQUE` (`SYS_ACCOUNT_CATEGORY_ID`)
);

INSERT INTO `money_category` VALUES (1,'Salary'),(2,'Rent/Housing'),(3,'Groceries'),(4,'Freelancing'),(5,'Transportation'),(6,'Utilities'),(7,'Entertainment'),(9,'Saving Account');
