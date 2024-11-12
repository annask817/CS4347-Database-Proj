CREATE TABLE User(
  user_id int NOT NULL,
  user_name varchar(15),
  user_type varchar(15),
  user_email varchar(64),
  time_created Date,
  PRIMARY KEY(user_id)
);

CREATE TABLE Expense(
  expense_id int NOT NULL,
  purpose varchar(50),
  PRIMARY KEY(expense_id)
);

CREATE TABLE Income(
  income_id int NOT NULL,
  source varchar(50),
  PRIMARY KEY(income_id)
);

CREATE TABLE Transactions(
  uid int NOT NULL,
  transaction_id int,
  dateOf Date,
  amount double,
  PRIMARY KEY(transaction_id),
  FOREIGN KEY(uid) REFERENCES User(user_id)
    ON UPDATE CASCADE  ON DELETE CASCADE
);

CREATE TABLE Transaction_Expense(
  trid int,
  exid int,
  FOREIGN KEY(trid) REFERENCES Transactions(transaction_id)
    ON UPDATE CASCADE  ON DELETE CASCADE,
  FOREIGN KEY(exid) REFERENCES Expense(expense_id)
    ON UPDATE CASCADE  ON DELETE CASCADE
);

CREATE TABLE Transaction_Income(
  trid int,
  inid int,
  FOREIGN KEY(trid) REFERENCES Transactions(transaction_id)
    ON UPDATE CASCADE  ON DELETE CASCADE,
  FOREIGN KEY(inid) REFERENCES Income(income_id)
    ON UPDATE CASCADE  ON DELETE CASCADE
);

CREATE TABLE Category(
  category_id int NOT NULL,
  category_name varchar(20),
  PRIMARY KEY(category_id)
);

CREATE TABLE Expense_Category(
  catid int DEFAULT 1,
  exid int,
  FOREIGN KEY(catid) REFERENCES Category(category_id)
    ON UPDATE CASCADE  ON DELETE SET DEFAULT,
  FOREIGN KEY(exid) REFERENCES Expense(expense_id)
    ON UPDATE CASCADE  ON DELETE CASCADE
);

CREATE TABLE Income_Category(
  catid int DEFAULT 1,
  inid int,
  FOREIGN KEY(catid) REFERENCES Category(category_id)
    ON UPDATE CASCADE  ON DELETE SET DEFAULT,
  FOREIGN KEY(inid) REFERENCES Income(income_id)
    ON UPDATE CASCADE  ON DELETE CASCADE
);

CREATE TABLE Recurring(
  recurring_id int NOT NULL,
  recurring_date DATE,
  PRIMARY KEY(recurring_id)
);

CREATE TABLE CanBe(
  trid int,
  reid int,
  FOREIGN KEY(trid) REFERENCES Transactions(transaction_id)
    ON UPDATE CASCADE  ON DELETE CASCADE,
  FOREIGN KEY(reid) REFERENCES Recurring(recurring_id)
    ON UPDATE CASCADE  ON DELETE CASCADE
);

CREATE TABLE Performs(
  uid int,
  trid int,
  FOREIGN KEY(uid) REFERENCES User(user_id)
    ON UPDATE CASCADE  ON DELETE CASCADE,
  FOREIGN KEY(trid) REFERENCES Transactions(transaction_id)
    ON UPDATE CASCADE  ON DELETE CASCADE
);
