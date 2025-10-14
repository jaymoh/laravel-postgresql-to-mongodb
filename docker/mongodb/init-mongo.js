db = db.getSiblingDB('laravel_blog');

db.createUser({
    user: 'laravel',
    pwd: 'secret',
    roles: [
        {
            role: 'readWrite',
            db: 'laravel_blog'
        }
    ]
});
