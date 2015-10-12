<?php
//connect to the cluster and keyspace smsplay

$cluster = Cassandra::cluster()
	->withPersistentSessions(true)
	->withTokenAwareRouting(true)
		->build();

$keyspace = 'smsplay';

$session = $cluster->connect($keyspace);


?>
