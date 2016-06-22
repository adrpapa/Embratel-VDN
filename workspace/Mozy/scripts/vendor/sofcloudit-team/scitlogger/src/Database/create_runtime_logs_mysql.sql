CREATE TABLE IF NOT EXISTS runtime_logs (
    id int not null auto_increment,
    time datetime not null,
    aps varchar(128) not null,
    account varchar(32),
    subscription varchar(32),
    level tinyint,
    label varchar(10),
    message text,
    context text,
    primary key(id)
) ENGINE = InnoDB;