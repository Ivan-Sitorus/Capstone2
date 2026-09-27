import LoginPage from '@/Components/Shared/LoginPage';
import { ShoppingBag } from 'lucide-react';

export default function Login() {
    return (
        <LoginPage
            action={route('kasir.login.attempt')}
            icon={ShoppingBag}
            title="minePOS"
            subtitle="Masuk ke sistem Point of Sale"
            dataInterface="cashier"
        />
    );
}
