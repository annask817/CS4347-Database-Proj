LOAD DATA INFILE 'user.csv'
INTO TABLE User
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES;

LOAD DATA INFILE 'expense.csv'
INTO TABLE Expense
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(expense_id, @purpose)
SET purpose = REPLACE(@purpose, '\r', '');

LOAD DATA INFILE 'income.csv'
INTO TABLE Income
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(income_id, @source)
SET source = REPLACE(@source, '\r', '');

LOAD DATA INFILE 'transactions.csv'
INTO TABLE Transactions
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(usid, transaction_id, dateOf, @amount)
SET amount = CAST(REPLACE(@amount, '\r', '') AS SIGNED);

LOAD DATA INFILE 'transaction_expense.csv'
INTO TABLE Transaction_Expense
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(trid, @exid_1)
SET exid = CAST(REPLACE(@exid_1, '\r', '') AS SIGNED);

LOAD DATA INFILE 'transaction_income.csv'
INTO TABLE Transaction_Income
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(trid, @inid_1)
SET inid = CAST(REPLACE(@inid_1, '\r', '') AS SIGNED);

LOAD DATA INFILE 'category.csv'
INTO TABLE Category
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(category_id, @category_name)
SET category_name = REPLACE(@category_name, '\r', '');

LOAD DATA INFILE 'expense_category.csv'
INTO TABLE Expense_Category
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(catid, @exid_2)
SET exid = CAST(REPLACE(@exid_2, '\r', '') AS SIGNED);

LOAD DATA INFILE 'income_category.csv'
INTO TABLE Income_Category
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(catid, @inid_2)
SET inid = CAST(REPLACE(@inid_2, '\r', '') AS SIGNED);

LOAD DATA INFILE 'performs.csv'
INTO TABLE Performs
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(usid, @trid)
SET trid = CAST(REPLACE(@trid, '\r', '') AS SIGNED);
