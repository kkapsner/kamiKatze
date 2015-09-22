<?php
/**
 *
 * @author kkapsner
 */
interface DBItemFieldGroupInterface extends DBItemFieldInterface{
	public function parseGroup($classSpecifier, $group);
}
