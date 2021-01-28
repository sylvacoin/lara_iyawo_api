<?php
namespace Deployer;
desc('Symlink Laravel public To public_html');
set ('ssh_multiplexing', false);
task('app:symlink_public_html', function () {
    run('ln -s /home/dimcksji/source/current/public /home/dimcksji/api.iyawosavings.com');
});
