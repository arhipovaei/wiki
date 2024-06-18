DROP TABLE IF EXISTS `cats`;
CREATE TABLE `cats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='статусы';

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL COMMENT 'содержимое комментария',
  `user_id` int(11) NOT NULL COMMENT 'автор комментария',
  `dt` datetime NOT NULL COMMENT 'дата и время комментария',
  `doc_id` int(11) NOT NULL COMMENT 'код документа',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='комментарии';

DROP TABLE IF EXISTS `docs`;
CREATE TABLE `docs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'код документа',
  `name` varchar(200) NOT NULL COMMENT 'наименование документа',
  `content` text NOT NULL COMMENT 'содержимое документа',
  `dt` datetime NOT NULL COMMENT 'дата и время создания',
  `user_id` int(11) NOT NULL COMMENT 'автор',
  `version` int(11) NOT NULL COMMENT 'версия',
  `last_dt` datetime NOT NULL COMMENT 'время последнего изменения',
  `cat_id` int(11) NOT NULL COMMENT 'код категории',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='документы';

DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fname` varchar(255) NOT NULL COMMENT 'имя файла',
  `fsize` int(11) NOT NULL COMMENT 'размер файла в байтах',
  `request_id` int(11) NOT NULL DEFAULT 0 COMMENT 'код документа',
  `dt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'время добавления',
  `user_id` int(11) NOT NULL COMMENT 'кем загружен файл',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `levels`;
CREATE TABLE `levels` (
  `id` int(11) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'дата новости',
  `note` text NOT NULL COMMENT 'текст новости',
  `user_id` int(11) NOT NULL DEFAULT 1 COMMENT 'кем добавлено',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='новости';


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fio` varchar(200) DEFAULT NULL COMMENT 'фио ',
  `rank` varchar(200) DEFAULT NULL COMMENT 'должность',
  `phone` varchar(15) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL,
  `login` varchar(30) DEFAULT NULL,
  `date_reg` timestamp NULL DEFAULT current_timestamp(),
  `email` varchar(200) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `birthday` date NOT NULL,
  `note` text NOT NULL COMMENT 'примечание',
  `department_id` int(11) NOT NULL COMMENT 'код отделения',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=234 DEFAULT CHARSET=utf8;