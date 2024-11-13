LOAD DATA INFILE 'data/user.csv'
INTO TABLE User
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES;

LOAD DATA INFILE 'data/expense.csv'
INTO TABLE Expense
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(expense_id, @purpose)
SET purpose = REPLACE(@purpose, '\r', '');

LOAD DATA INFILE 'data/income.csv'
INTO TABLE Income
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(income_id, @source)
SET source = REPLACE(@source, '\r', '');

LOAD DATA INFILE 'data/transactions.csv'
INTO TABLE Transactions
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(uid, transaction_id, dateOf, @amount)
SET amount = CAST(REPLACE(@amount, '\r', '') AS SIGNED);

LOAD DATA INFILE 'data/transaction_expense.csv'
INTO TABLE Transaction_Expense
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(trid, @exid_1)
SET exid = CAST(REPLACE(@exid_1, '\r', '') AS SIGNED);

LOAD DATA INFILE 'data/transaction_income.csv'
INTO TABLE Transaction_Income
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(trid, @inid_1)
SET inid = CAST(REPLACE(@inid_1, '\r', '') AS SIGNED);

LOAD DATA INFILE 'data/category.csv'
INTO TABLE Category
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(category_id, @category_name)
SET category_name = REPLACE(@category_name, '\r', '');

LOAD DATA INFILE 'data/expense_category.csv'
INTO TABLE Expense_Category
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(catid, @exid_2)
SET exid = CAST(REPLACE(@exid_2, '\r', '') AS SIGNED);

LOAD DATA INFILE 'data/income_category.csv'
INTO TABLE Income_Category
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(catid, @inid_2)
SET inid = CAST(REPLACE(@inid_2, '\r', '') AS SIGNED);

LOAD DATA INFILE 'data/recurring.csv'
INTO TABLE Recurring
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES;

LOAD DATA INFILE 'data/canbe.csv'
INTO TABLE CanBe
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(trid, @reid)
SET reid = CAST(REPLACE(@reid, '\r', '') AS SIGNED);

LOAD DATA INFILE 'data/performs.csv'
INTO TABLE Performs
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(uid, @trid)
SET trid = CAST(REPLACE(@trid, '\r', '') AS SIGNED);
