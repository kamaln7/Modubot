<?php

Class Modubot_Todo extends Modubot_Module {
	public $helpline = 'a todo system. pretty obvious, isn\'t it?';

	public function process(&$that, &$socket, $data, $input, $command, $args){
        $sender = $that->sender($data);

		$options = explode(' ', $args);
		$option = $options[0];
		unset($options[0]);
		$text = implode(' ', $options);
		if(!empty($option)){
			try {
				switch($option){
					case 'list':
						$list = $that->db->prepare('SELECT `id`, `item` FROM `todo` WHERE `nick` = :nick');
						$list->bindParam(':nick', strtolower($sender), PDO::PARAM_STR);
						$list->execute();

						if($list->rowCount() > 0){
							while($row = $list->fetch()){
								$this->notice($socket, $sender, "{$row->id}. {$row->item}");
								usleep(25000);
							}
						}else 
							$this->notice($socket, $sender, 'Your todo list is empty.');
					break;
					case 'add':
						if(!empty($text)){
							$add = $that->db->prepare('INSERT INTO `todo` (`nick`, `item`) VALUES (:nick, :item)');
							$add->bindParam(':nick', strtolower($sender), PDO::PARAM_STR);
							$add->bindParam(':item', $text, PDO::PARAM_STR);
							$add->execute();

							if($add->rowCount() > 0)
								$this->notice($socket, $sender, 'Todo added successfully.');
							else
								throw new PDOException('');
						}else
							$this->notice($socket, $sender, "Usage: {$that->prefix}todo add uy some eggs.");
					break;
					case 'del':
					case 'delete':
						if(!empty($text)){
							$delete = $that->db->prepare('DELETE FROM `todo` WHERE `id` = :id AND `nick` = :nick');
							$delete->bindParam(':id', $text, PDO::PARAM_INT);
							$delete->bindParam(':nick', strtolower($sender), PDO::PARAM_STR);
							$delete->execute();

							if($delete->rowCount() > 0)
								$this->notice($socket, $sender, 'Todo deleted successfully.');
							else
								$this->notice($socket, $sender, 'Todo ID does not exist or does not belong to your nickname :<');
						}else
							$this->notice($socket, $sender, "Usage: {$that->prefix}todo delete ID. You can get the ID from {$that->prefix}todo list.");
					break;
					case 'search':
						if(!empty($text)){
							$search = $that->db->prepare('SELECT `id`, `item`, MATCH (`item`) AGAINST (:text) AS `score` FROM `todo` WHERE `nick` = :nick AND MATCH (`item`) AGAINST (:text) ORDER BY `score` DESC');
							$search->bindParam(':text', $text, PDO::PARAM_STR);
							$search->bindParam(':nick', strtolower($sender), PDO::PARAM_STR);
							$search->execute();

							if($search->rowCount() > 0){
								while($row = mysql_fetch_object($search)){
									$this->notice($socket, $sender, "{$row->id}. {$row->item}");
									usleep(50000);
								}
							}else 
								$this->notice($socket, $sender, 'No matches found.');
						}else 
							$this->notice($socket, $sender, "Usage: {$that->prefix}todo search bug fix");
					break;
					case 'clear':
						$clear = $that->db->prepare('DELETE FROM `todo` WHERE `nick` = :nick');
						$clear->bindParam(':nick', strtolower($sender), PDO::PARAM_STR);
						$clear->execute();

						if($clear->rowCount() > 0)
							$this->notice($socket, $sender, 'Todo list cleared.');
						else
							$this->notice($socket, $sender, 'You do not have any items in your todo list.');
					break;

					default:
						$this->notice($socket, $sender, 'Available options: list, add, del(ete), search, clear.');
					break;
				}
			}catch (PDOException $e){
				$that->logE($e);
				$this->notice($socket, $sender, 'An error occurred :<');
			}
		}else {
			$this->notice($socket, $sender, "Usage: {$that->prefix}todo option");
		}
	}
}
