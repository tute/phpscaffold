function show_hint() {
	$('#sql').val("CREATE TABLE `users_test` (\n  `id` int(10) NOT NULL auto_increment,\n  `email` varchar(100) NOT NULL,\n  `pass` varchar(32) NOT NULL,\n  `curriculum` text NOT NULL,\n  `is_admin` int(1) NOT NULL,\n  `last_login` datetime NOT NULL,\n  `created` date NOT NULL,\n  PRIMARY KEY (`id`)\n);");
}
