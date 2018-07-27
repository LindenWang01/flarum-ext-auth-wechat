import { extend } from 'flarum/extend';
import app from 'flarum/app';
import LogInButtons from 'flarum/components/LogInButtons';
import SettingsPage from 'flarum/components/SettingsPage';
import WechatLoginButton from 'ryccheen/auth/wechat/WechatLoginButton';
import WechatBindButton from 'ryccheen/auth/wechat/WechatBindButton';

app.initializers.add('ryccheen-auth-wechat', () => {
  extend(LogInButtons.prototype, 'items', function(items) {
    items.add('wechat',
      <WechatLoginButton
        className="Button LogInButton--wechat"
        icon="wechat"
        path="/auth/wechat">
        {app.translator.trans('ryccheen-auth-wechat.forum.log_in.with_wechat_button')}
      </WechatLoginButton>
    );
  });
});

app.initializers.add('ryccheen-bind-wechat', () => {
  extend(SettingsPage.prototype, 'accountItems', function(items) {
    items.add('wechatBind',
      <WechatBindButton
        className="Button BindWechatButton--wechat"
        icon="wechat"
        path="/bind/wechat">
        {app.translator.trans('ryccheen-auth-wechat.forum.bind.with_wechat_button')}
      </WechatBindButton>
    );
  });
});
