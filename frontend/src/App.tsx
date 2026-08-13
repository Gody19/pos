import { Routes, Route, Navigate } from 'react-router-dom';
import { DashboardLayout } from './layouts/DashboardLayout';
import { ProtectedRoute, OpenShiftGuard } from './components/ProtectedRoute';
import { LoginPage } from './pages/LoginPage';
import { ForgotPasswordPage } from './pages/ForgotPasswordPage';
import { DashboardPage } from './pages/DashboardPage';
import { OpenShiftPage } from './pages/OpenShiftPage';
import { CloseShiftPage } from './pages/CloseShiftPage';
import { POSPage } from './pages/POSPage';
import { ReceiptPreviewPage } from './pages/ReceiptPreviewPage';
import { ProductsPage } from './pages/ProductsPage';
import { ProductFormPage } from './pages/ProductFormPage';
import { CategoriesPage } from './pages/CategoriesPage';
import { StockAdjustmentPage } from './pages/StockAdjustmentPage';
import { CustomersPage } from './pages/CustomersPage';
import { CustomerProfilePage } from './pages/CustomerProfilePage';
import { DailySalesReportPage } from './pages/DailySalesReportPage';
import { MonthlySalesReportPage } from './pages/MonthlySalesReportPage';
import { InventoryReportPage } from './pages/InventoryReportPage';
import { ShiftReportPage } from './pages/ShiftReportPage';
import { TopProductsPage } from './pages/TopProductsPage';
import { UsersPage } from './pages/UsersPage';
import { RolesPage } from './pages/RolesPage';
import { SettingsPage } from './pages/SettingsPage';

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/forgot-password" element={<ForgotPasswordPage />} />

      <Route element={<ProtectedRoute />}>
        <Route element={<DashboardLayout />}>
          {/* Shifts */}
          <Route element={<OpenShiftGuard />}>
            <Route path="/shift/open" element={<OpenShiftPage />} />
          </Route>
          <Route path="/shift/close" element={<CloseShiftPage />} />

          {/* POS */}
          <Route path="/pos" element={<POSPage />} />
          <Route path="/receipt/:saleId" element={<ReceiptPreviewPage />} />

          {/* Dashboard */}
          <Route path="/dashboard" element={<DashboardPage />} />

          {/* Inventory */}
          <Route path="/products" element={<ProductsPage />} />
          <Route path="/products/new" element={<ProductFormPage />} />
          <Route path="/products/:id/edit" element={<ProductFormPage />} />
          <Route path="/categories" element={<CategoriesPage />} />
          <Route path="/stock-adjustments" element={<StockAdjustmentPage />} />

          {/* Customers */}
          <Route path="/customers" element={<CustomersPage />} />
          <Route path="/customers/:id" element={<CustomerProfilePage />} />

          {/* Reports */}
          <Route path="/reports/daily-sales" element={<DailySalesReportPage />} />
          <Route path="/reports/monthly-sales" element={<MonthlySalesReportPage />} />
          <Route path="/reports/top-products" element={<TopProductsPage />} />
          <Route path="/reports/shift-summary" element={<ShiftReportPage />} />
          <Route path="/reports/inventory" element={<InventoryReportPage />} />

          {/* Admin */}
          <Route element={<ProtectedRoute roles={['admin']} />}>
            <Route path="/admin/users" element={<UsersPage />} />
            <Route path="/admin/roles" element={<RolesPage />} />
            <Route path="/admin/settings" element={<SettingsPage />} />
          </Route>
        </Route>
      </Route>

      <Route path="*" element={<Navigate to="/dashboard" replace />} />
    </Routes>
  );
}