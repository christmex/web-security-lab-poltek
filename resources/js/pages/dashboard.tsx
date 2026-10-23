import { Head, usePage } from '@inertiajs/react';
import { dashboard } from '@/routes';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
};

export default function Dashboard() {
    const { auth } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-semibold tracking-tight">
                    Selamat datang, {auth.user.name}
                </h1>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
