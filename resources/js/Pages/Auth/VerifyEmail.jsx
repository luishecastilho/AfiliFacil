import AuthLayout from '@/Layouts/AuthLayout';
import { Button } from '@/Components/ui/Button';
import { Head, Link, useForm } from '@inertiajs/react';

export default function VerifyEmail({ status }) {
    const { post, processing } = useForm({});

    const submit = (e) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    return (
        <AuthLayout
            title="Verify your email"
            description="We've sent a verification link to your email address."
        >
            <Head title="Email Verification" />

            {status === 'verification-link-sent' && (
                <div className="mb-4 rounded-md bg-green-50 p-3 text-center text-sm font-medium text-green-700">
                    A new verification link has been sent to your email address.
                </div>
            )}

            <form onSubmit={submit} className="flex flex-col gap-4">
                <Button type="submit" className="w-full" disabled={processing}>
                    Resend verification email
                </Button>

                <Link
                    href={route('logout')}
                    method="post"
                    as="button"
                    className="text-center text-sm text-muted-foreground underline underline-offset-4 hover:text-foreground"
                >
                    Log out
                </Link>
            </form>
        </AuthLayout>
    );
}
