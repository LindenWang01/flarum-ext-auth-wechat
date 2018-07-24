<?php
/*
 * This file is part of Flarum.
 *
 * (c) RycCheen <xls9009@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Flarum\Database\Migration;

return Migration::addColumns('users', [
    'wechat_id' => ['string', 'length' => 64, 'nullable' => true]
]);
