<?php 
	require_once "include/db_configure.php";
 	$sql = "SELECT p.domain, u.*, b.cnt FROM
			(
			    SELECT * FROM (
			        SELECT provider_id, count(provider_id) cnt
			        FROM `user_provider`
			        WHERE `user_id` = 2
			        group by user_id, provider_id
			    ) a 
				where a.cnt >= 2
			) b left join user_provider u on b.provider_id = u.provider_id
			left join providers p on b.provider_id = p.id
			WHERE u.is_valid_key = 0";
	$result = $conn->query($sql);
	$str = "";
	while($row = $result->fetch_assoc()){
		$str .= $row['id'] . ", ";
	}
	echo $str;

?>