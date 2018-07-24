<?php
/*
 * This file is part of Flarum.
 *
 * (c) RycCheen <xls9009@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RycCheen\Auth\Wechat;

use Flarum\Forum\AuthenticationResponseFactory;
use Flarum\Forum\Controller\AbstractOAuth2Controller;
use Flarum\Settings\SettingsRepositoryInterface;
use RycCheen\OAuth2\Client\Provider\Wechat;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class WechatAuthController extends AbstractOAuth2Controller
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @param AuthenticationResponseFactory $authResponse
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(AuthenticationResponseFactory $authResponse, SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
        $this->authResponse = $authResponse;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProvider($redirectUri)
    {
        return new Wechat([
            'appid'        => $this->settings->get('ryccheen-auth-wechat.app_id'),
            'secret'       => $this->settings->get('ryccheen-auth-wechat.app_secret'),
            'redirect_uri' => $redirectUri
        ]);
    }

    /**
     * {@inheritdoc}
     * scope: 应用授权作用域，拥有多个作用域用逗号（,）分隔，网页应用目前仅填写snsapi_login
     */
    protected function getAuthorizationUrlOptions()
    {
        return [
            'scope' => 'snsapi_login'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdentification(ResourceOwnerInterface $resourceOwner)
    {
        return [
            'wechat_id' => $resourceOwner->getUnionId()
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSuggestions(ResourceOwnerInterface $resourceOwner)
    {
        return [
            'username' => $resourceOwner->getUnionId(),
            'avatarUrl' => $resourceOwner->getHeadImgUrl()
        ];
    }
}
