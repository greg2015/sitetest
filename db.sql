SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--CREATE DATABASE IF NOT EXISTS `gdelillo_db` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
--USE `gdelillo_db`;

DELIMITER $$
DROP FUNCTION IF EXISTS `ADD_PHOTO`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `ADD_PHOTO`(`_user` INT, `_url` VARCHAR(100)) RETURNS int(11)
    NO SQL
BEGIN
UPDATE profile SET photo = _url WHERE idUser = _user;
RETURN TRUE;
END$$

DROP FUNCTION IF EXISTS `ALREADY_WANT_BE_FRIEND`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `ALREADY_WANT_BE_FRIEND`(`_user` INT, `_other` INT) RETURNS tinyint(1)
    NO SQL
BEGIN
DECLARE already BOOLEAN DEFAULT FALSE;
SELECT TRUE INTO already FROM friendship
WHERE (idUser = _user) AND (idFriend = _other);
RETURN already;
END$$

DROP FUNCTION IF EXISTS `BE_FRIEND`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `BE_FRIEND`(`_user` INT, `_friend` INT) RETURNS tinyint(1)
    NO SQL
BEGIN
DECLARE is_friend BOOLEAN DEFAULT FALSE;
SELECT TRUE INTO is_friend FROM friendship
WHERE 
(((idUser = _user) AND (idFriend = _friend)) OR ((idUser = _friend) AND (idFriend = _user) AND (agree = TRUE)));
IF is_friend THEN RETURN FALSE;
END IF;
SELECT TRUE INTO is_friend FROM friendship WHERE ((idUser = _friend) AND (idFriend = _user));
IF is_friend THEN
UPDATE friendship SET agree = TRUE WHERE ((idUser = _friend) AND (idFriend = _user));
ELSE INSERT INTO friendship(idUser,idFriend,agree) VALUES (_user,_friend,FALSE);
END IF;
RETURN TRUE;
END$$

DROP FUNCTION IF EXISTS `DELETE_PHOTO`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `DELETE_PHOTO`(`_user` INT) RETURNS int(11)
    NO SQL
BEGIN
UPDATE profile SET photo = NULL WHERE idUser = _user;
RETURN TRUE;
END$$

DROP FUNCTION IF EXISTS `DONT_BE_FRIEND`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `DONT_BE_FRIEND`(`_user` INT, `_dmd` INT) RETURNS tinyint(1)
    NO SQL
BEGIN
DELETE FROM friendship WHERE (idUser = _user AND idFriend = _dmd) OR (idUser = _dmd AND idFriend = _user);
RETURN TRUE;
END$$

DROP FUNCTION IF EXISTS `IS_FRIEND`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `IS_FRIEND`(`_user` INT, `_other` INT) RETURNS tinyint(1)
    NO SQL
BEGIN
DECLARE is_friend BOOLEAN DEFAULT FALSE;
SELECT TRUE INTO is_friend FROM friendship WHERE ((idUser = _user AND idFriend = _other) OR (idUser = _other AND idFriend = _user)) AND (agree = TRUE);
RETURN is_friend;
END$$

DROP FUNCTION IF EXISTS `LOGIN`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `LOGIN`(`_name` VARCHAR(30), `_password` VARCHAR(100)) RETURNS int(11)
    NO SQL
BEGIN
DECLARE login INT DEFAULT NULL;
SELECT u.idUser INTO login FROM user u, profile p
WHERE u.idUser = p.idUser
AND ((u.mail LIKE _name) OR (p.pseudo LIKE _name))
AND u.password LIKE _password;
RETURN login;
END$$

DROP FUNCTION IF EXISTS `PERSIST_POST`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `PERSIST_POST`(`_id` INT, `_user` INT, `_wall` INT, `_message` TEXT) RETURNS tinyint(1)
    NO SQL
    DETERMINISTIC
BEGIN
DECLARE exist BOOLEAN DEFAULT FALSE;
IF _id IS NULL THEN
INSERT INTO post(idUser,idWall,message) VALUES(_user,_wall,_message);
RETURN TRUE;
END IF;
SELECT TRUE INTO exist FROM post WHERE idPost = _id LIMIT 1;
IF exist THEN
UPDATE post SET message = CONCAT(_message,' [modifié le ',NOW(),']')
WHERE idPost = _id;
ELSE RETURN FALSE;
END IF;
RETURN TRUE;
END$$

DROP FUNCTION IF EXISTS `SIGNUP`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `SIGNUP`(`_mail` VARCHAR(30), `_password` VARCHAR(100), `_pseudo` VARCHAR(30), `_alias` VARCHAR(100), `_age` TINYINT(3) UNSIGNED, `_description` TEXT, `_sexe` CHAR(1)) RETURNS tinyint(1)
    NO SQL
BEGIN
DECLARE same_pseudo BOOLEAN DEFAULT FALSE;
DECLARE first_post BOOLEAN DEFAULT FALSE;
SELECT TRUE INTO same_pseudo FROM profile p WHERE p.pseudo LIKE LOWER(_pseudo);
IF same_pseudo THEN RETURN FALSE;
END IF;
INSERT INTO user (mail,password) VALUES (_mail,_password);
INSERT INTO profile (pseudo,alias,age,description,sexe)
VALUES(LOWER(_pseudo),CONCAT('@',_alias),_age,_description,UPPER(_sexe));
SELECT `PERSIST_POST`(NULL,LAST_INSERT_ID(),NULL,'à rejoint FaceKikou !') INTO first_post ;
RETURN first_post;
END$$

DELIMITER ;

DROP TABLE IF EXISTS `friendship`;
CREATE TABLE IF NOT EXISTS `friendship` (
  `idUser` int(11) NOT NULL,
  `idFriend` int(11) NOT NULL,
  `agree` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

TRUNCATE TABLE `friendship`;
INSERT INTO `friendship` (`idUser`, `idFriend`, `agree`) VALUES
(18, 17, 1),
(20, 17, 1);

DROP TABLE IF EXISTS `post`;
CREATE TABLE IF NOT EXISTS `post` (
`idPost` int(11) NOT NULL,
  `idUser` int(11) NOT NULL,
  `idWall` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;

TRUNCATE TABLE `post`;
INSERT INTO `post` (`idPost`, `idUser`, `idWall`, `message`, `date`) VALUES
(27, 17, NULL, 'à rejoint FaceKikou !', '2015-03-24 15:18:46'),
(28, 18, NULL, 'à rejoint FaceKikou !', '2015-03-24 15:20:13'),
(29, 19, NULL, 'à rejoint FaceKikou !', '2015-03-24 15:21:02'),
(30, 20, NULL, 'à rejoint FaceKikou !', '2015-03-24 16:02:52'),
(31, 20, NULL, 'hello world !\r\n', '2015-03-24 16:04:38'),
(33, 20, 17, 'gros con !', '2015-03-24 16:08:30'),
(35, 18, NULL, 'sdfsdfg', '2015-04-06 20:28:47'),
(40, 18, NULL, '<img alt class="pp" src="http://blog.mrwebmaster.it/files/2012/07/php.png">', '2015-04-06 20:38:30'),
(41, 18, 17, 'sdqsd', '2015-04-06 20:58:32'),
(42, 18, NULL, '<img alt class="pp" src="http://media.meltyfood.fr/article-992527-ajust_930/coco-pops.jpg">', '2015-04-07 15:01:02');

DROP TABLE IF EXISTS `profile`;
CREATE TABLE IF NOT EXISTS `profile` (
`idUser` int(11) NOT NULL,
  `pseudo` varchar(30) NOT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `age` tinyint(3) unsigned DEFAULT NULL,
  `description` text,
  `sexe` char(1) DEFAULT NULL,
  `photo` varchar(300) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

TRUNCATE TABLE `profile`;
INSERT INTO `profile` (`idUser`, `pseudo`, `alias`, `age`, `description`, `sexe`, `photo`) VALUES
(17, 'louis', '@louis', 20, 'je suis posey !', 'M', NULL),
(18, 'gregoire', '@gregou', 20, 'j''aime les chips !', 'M', NULL),
(19, 'coco', '@cocopops', 80, 'c''est fort en chocolat !', 'F', NULL),
(20, 'ttttt', '@ttttttttttttt', 45, 'tg !', NULL, NULL);

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
`idUser` int(11) NOT NULL,
  `mail` varchar(30) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

TRUNCATE TABLE `user`;
INSERT INTO `user` (`idUser`, `mail`, `password`) VALUES
(17, 'louis@test.com', '125c33ae50c34eb06305afddc77ca956f6ebc5cc'),
(18, 'gregoire@test.com', 'ad770ac8047ce724115c25f4ec607a3b0cdacc91'),
(19, 'coco@test.com', '3006a67aad5155b804df5983586a46b5b79748a9'),
(20, 't@t.com', '9d46294658f753d5a6df684ba77378222aac0888');
DROP VIEW IF EXISTS `user_view`;
CREATE TABLE IF NOT EXISTS `user_view` (
`idUser` int(11)
,`mail` varchar(30)
,`pseudo` varchar(30)
,`alias` varchar(100)
,`age` tinyint(3) unsigned
,`description` text
,`sexe` char(1)
,`photo` varchar(300)
);DROP TABLE IF EXISTS `user_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_view` AS select `u`.`idUser` AS `idUser`,`u`.`mail` AS `mail`,`p`.`pseudo` AS `pseudo`,`p`.`alias` AS `alias`,`p`.`age` AS `age`,`p`.`description` AS `description`,`p`.`sexe` AS `sexe`,`p`.`photo` AS `photo` from (`user` `u` join `profile` `p`) where (`u`.`idUser` = `p`.`idUser`);


ALTER TABLE `friendship`
 ADD PRIMARY KEY (`idUser`,`idFriend`), ADD KEY `idFriend` (`idFriend`);

ALTER TABLE `post`
 ADD PRIMARY KEY (`idPost`), ADD KEY `idUser` (`idUser`), ADD KEY `idWall` (`idWall`);

ALTER TABLE `profile`
 ADD PRIMARY KEY (`idUser`), ADD UNIQUE KEY `pseudo` (`pseudo`);

ALTER TABLE `user`
 ADD PRIMARY KEY (`idUser`);


ALTER TABLE `post`
MODIFY `idPost` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=43;
ALTER TABLE `profile`
MODIFY `idUser` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=21;
ALTER TABLE `user`
MODIFY `idUser` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=21;

ALTER TABLE `friendship`
ADD CONSTRAINT `friendship_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`),
ADD CONSTRAINT `friendship_ibfk_2` FOREIGN KEY (`idFriend`) REFERENCES `user` (`idUser`);

ALTER TABLE `post`
ADD CONSTRAINT `Post_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`),
ADD CONSTRAINT `Post_ibfk_2` FOREIGN KEY (`idWall`) REFERENCES `user` (`idUser`);

ALTER TABLE `profile`
ADD CONSTRAINT `Profile_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
