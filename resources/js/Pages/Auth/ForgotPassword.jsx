import InputError from '@/Components/InputError';
import AuthLayout from '@/Layouts/AuthLayout';
import { Button } from '@/Components/ui/Button';
import { Input } from '@/Components/ui/Input';
import { Label } from '@/Components/ui/Label';
import { Head, Link, useForm } from '@inertiajs/react';

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <AuthLayout
            title="Forgot password?"
            description="Enter your email and we'll send you a reset link"
        >
            <Head title="Forgot Password" />

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>
            )}

            <form onSubmit={submit} className="flex flex-col gap-6">
                <div className="grid gap-2">
                    <Label htmlFor="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        autoFocus
                        placeholder="you@example.com"
                        onChange={(e) => setData('email', e.target.value)}
                    />
                    <InputError message={errors.email} />
                </div>

                <Button type="submit" className="w-full" disabled={processing}>
                    Email password reset link
                </Button>
            </form>

            <div className="mt-6 text-center text-sm text-muted-foreground">
                <Link href={route('login')} className="text-foreground underline underline-offset-4">
                    Back to log in
                </Link>
            </div>
        </AuthLayout>
    );
}
