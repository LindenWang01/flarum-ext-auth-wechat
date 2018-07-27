<?php
/*
 * This file is part of Flarum.
 *
 * (c) RycCheen <xls9009@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RycCheen\Auth\Wechat\Listener;

use Flarum\Event\ConfigureForumRoutes;
use Illuminate\Contracts\Events\Dispatcher;

class AddWechatAuthRoute
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(ConfigureForumRoutes::class, [$this, 'configureForumRoutes']);
    }

    /**
     * @param ConfigureForumRoutes $event
     */
    public function configureForumRoutes(ConfigureForumRoutes $event)
    {
        $event->get('/auth/wechat', 'auth.wechat', 'RycCheen\Auth\Wechat\WechatAuthController');
        $event->get('/bind/wechat', 'bind.wechat', 'RycCheen\Auth\Wechat\WechatBindController');
    }
}
