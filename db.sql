CREATE DATABASE zeacurity;
USE zeacurity;

CREATE TABLE blacklist (
    id bigint(20) unsigned not null auto_increment primary key,
    ip varchar(128) not null,
    notes text,
    created datetime not null,
    deleted datetime default null
);