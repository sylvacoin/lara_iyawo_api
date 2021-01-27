<?php
namespace Deployer;
desc('Symlink Laravel public To public_html');
task('app:symlink_public_html', function () {
    run('ln -s /home/{{your-cpanel-username}}/source/current/public /home/{{your-cpanel-username}}/public_html');
});
