# Laravel Inertia React Setup

This project is configured with Laravel, Inertia.js, and React.

## Setup Complete! 🎉

Your Laravel Inertia React application is now fully configured.

### What's Been Installed

- **React & React DOM**: Core React libraries
- **@inertiajs/react**: Inertia.js React adapter
- **@vitejs/plugin-react**: Vite plugin for React
- **Tailwind CSS**: Utility-first CSS framework
- **Ziggy**: Laravel route helper for JavaScript

### File Structure

```
resources/
├── js/
│   ├── Pages/          # React page components
│   │   └── Welcome.jsx
│   ├── Components/     # Reusable React components
│   ├── Layouts/        # Layout components
│   │   └── GuestLayout.jsx
│   ├── app.jsx         # Inertia app initialization
│   └── bootstrap.js    # Bootstrap file
├── css/
│   └── app.css         # Tailwind CSS directives
└── views/
    └── app.blade.php   # Root Blade template
```

### Configuration Files

- **vite.config.js**: Configured with React plugin and path aliases
- **tailwind.config.js**: Tailwind CSS configuration
- **postcss.config.js**: PostCSS configuration
- **jsconfig.json**: JavaScript/React IDE support

### Key Files Created/Modified

1. **HandleInertiaRequests.php**: Middleware for sharing data across all pages
2. **app/Http/Kernel.php**: Added Inertia middleware to web group
3. **resources/views/app.blade.php**: Root Blade template
4. **resources/js/app.jsx**: Inertia app entry point
5. **routes/web.php**: Updated to use Inertia::render()

## Running the Application

### Development

Run both Laravel and Vite dev servers:

```bash
# Terminal 1 - Laravel server
php artisan serve

# Terminal 2 - Vite dev server
npm run dev
```

Then visit: http://localhost:8000

### Building for Production

```bash
npm run build
```

## Creating New Pages

1. Create a new component in `resources/js/Pages/`:

```jsx
// resources/js/Pages/About.jsx
import { Head } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';

export default function About() {
    return (
        <GuestLayout>
            <Head title="About" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <h1 className="text-3xl font-bold">About Page</h1>
                </div>
            </div>
        </GuestLayout>
    );
}
```

2. Add a route in `routes/web.php`:

```php
use Inertia\Inertia;

Route::get('/about', function () {
    return Inertia::render('About');
});
```

## Using Inertia Links

Use `<Link>` component for navigation:

```jsx
import { Link } from '@inertiajs/react';

<Link href="/about">About</Link>
```

## Passing Data from Laravel to React

```php
// In your route or controller
return Inertia::render('PageName', [
    'user' => $user,
    'posts' => Post::all(),
]);
```

Access in React component:

```jsx
export default function PageName({ user, posts }) {
    return (
        <div>
            <h1>Welcome {user.name}</h1>
            {posts.map(post => (
                <div key={post.id}>{post.title}</div>
            ))}
        </div>
    );
}
```

## Shared Data

Data shared across all pages is defined in `app/Http/Middleware/HandleInertiaRequests.php`:

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user(),
        ],
    ];
}
```

## Path Aliases

You can use `@/` as an alias for `resources/js/`:

```jsx
import GuestLayout from '@/Layouts/GuestLayout';
import Button from '@/Components/Button';
```

## Useful Resources

- [Inertia.js Documentation](https://inertiajs.com)
- [React Documentation](https://react.dev)
- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Ziggy Documentation](https://github.com/tighten/ziggy)
