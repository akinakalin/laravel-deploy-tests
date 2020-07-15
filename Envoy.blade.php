@servers(['web' => 'deployer@ec2-18-185-72-26.eu-central-1.compute.amazonaws.com'])

@setup
    $repository = 'git@gitlab.com:akinakalin/laravel-deploy-tests.git';
    $releases_dir = '/var/www/app/releases';
    $app_dir = '/var/www/app';
    $release = date('YmdHis');
    $new_release_dir = $releases_dir .'/'. $release;
    $current_dir = $app_dir . '/current';
@endsetup

@story('deploy')
    clone
    run_composer
    update_symlinks
    artisan_commands
    clean_old_releases
@endstory

@task('clone')
    echo 'Cloning repository'
    [ -d {{ $releases_dir }} ] || mkdir {{ $releases_dir }}
    git clone --depth 1 {{ $repository }} {{ $new_release_dir }}
    cd {{ $new_release_dir }}
    git reset --hard {{ $commit }}
@endtask

@task('run_composer')
    echo "Starting deployment ({{ $release }})"
    cd {{ $new_release_dir }}
    composer install --prefer-dist --no-scripts
@endtask

@task('update_symlinks')
    echo "Linking storage directory"
    rm -rf {{ $new_release_dir }}/storage
    ln -nfs {{ $app_dir }}/storage {{ $new_release_dir }}/storage

    echo "Linking .env file"
    ln -nfs {{ $app_dir }}/.env {{ $new_release_dir }}/.env

    echo "Chmod bootstrap directory"
    chmod -R 777 {{ $new_release_dir }}/bootstrap/cache

    echo "Linking current release"
    ln -nfs {{ $new_release_dir }} {{ $current_dir }}
@endtask

@task('clean_old_releases')
    echo "Cleaning old releases"
    ls -dt {{ $releases_dir }}/* | tail -n +4 | xargs -d "\n" rm -rf;
@endtask

@task('artisan_commands')
    echo "Clear cache"
    php {{ $current_dir }}/artisan cache:clear
@endtask

@finished
    echo "Envoy deployment script finished. \r\n";
@endfinished
