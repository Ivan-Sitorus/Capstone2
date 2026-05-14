import LoginPage from '@/Components/Shared/LoginPage';
import { ShoppingBag } from 'lucide-react';

export default function CashierLogin() {
    return (
        <LoginPage
            action="/cashier/login"
            icon={ShoppingBag}
            title="W9 Cafe POS"
            subtitle="Masuk ke sistem Point of Sale"
            dataInterface="cashier"
        />
    );
}
