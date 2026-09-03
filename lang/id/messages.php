<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Authentication
    |--------------------------------------------------------------------------
    */
    'auth' => [
        'login' => 'Login successful.',
        'logout' => 'Logout successful.',
        'failed' => 'These credentials do not match our records.',
        'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
        'password_reset_link_sent' => 'A password reset link has been sent to your email.',
        'reset' => 'Your password has been reset.',
        'confirm_password' => 'The password you entered is incorrect.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - General
    |--------------------------------------------------------------------------
    */
    'general' => [
        'created' => 'Data created successfully.',
        'updated' => 'Data updated successfully.',
        'deleted' => 'Data deleted successfully.',
        'saved' => 'Data saved successfully.',
        'error' => 'An error occurred. Please try again.',
        'not_found' => 'Data not found.',
        'required' => 'This field is required.',
        'success' => 'Operation successful.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Products
    |--------------------------------------------------------------------------
    */
    'products' => [
        'created' => 'Product added successfully.',
        'updated' => 'Product data updated successfully.',
        'deleted' => 'Product deleted successfully.',
        'not_found' => 'Product not found.',
        'import_success' => 'Product import successful. :count products imported.',
        'import_error' => 'Product import failed. Please check the file format.',
        'export_success' => 'Product export successful.',
        'barcode_generated' => 'Barcode generated successfully.',
        'stock_insufficient' => 'Insufficient stock.',
        'min_stock_reached' => 'Minimum stock reached.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Categories
    |--------------------------------------------------------------------------
    */
    'categories' => [
        'created' => 'Category added successfully.',
        'updated' => 'Category updated successfully.',
        'deleted' => 'Category deleted successfully.',
        'not_found' => 'Category not found.',
        'has_products' => 'Category cannot be deleted because it still has products.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Customers
    |--------------------------------------------------------------------------
    */
    'customers' => [
        'created' => 'Customer added successfully.',
        'updated' => 'Customer data updated successfully.',
        'deleted' => 'Customer deleted successfully.',
        'not_found' => 'Customer not found.',
        'import_success' => 'Customer import successful.',
        'import_error' => 'Customer import failed.',
        'segments_synced' => 'Customer manual segments updated successfully.',
        'upgraded_to_member' => 'Customer upgraded to member successfully.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Suppliers
    |--------------------------------------------------------------------------
    */
    'suppliers' => [
        'created' => 'Supplier added successfully.',
        'updated' => 'Supplier data updated successfully.',
        'deleted' => 'Supplier deleted successfully.',
        'not_found' => 'Supplier not found.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Transactions
    |--------------------------------------------------------------------------
    */
    'transactions' => [
        'created' => 'Transaction saved successfully.',
        'product_added' => 'Product added successfully.',
        'product_not_found' => 'Product not found.',
        'stock_insufficient' => 'Insufficient stock.',
        'cart_empty' => 'Cart is empty.',
        'hold_success' => 'Transaction held: :label',
        'resume_success' => 'Transaction resumed.',
        'hold_cleared' => 'Held transaction cleared successfully.',
        'hold_not_found' => 'Held transaction not found.',
        'active_cart_exists' => 'Please complete or hold the active transaction first.',
        'pending_approval' => 'Transaction is pending supervisor approval.',
        'payment_confirmed' => 'Payment confirmed successfully.',
        'already_paid' => 'Transaction has already been paid.',
        'share_success' => 'Transaction shared successfully.',
        'print_success' => 'Receipt is ready to print.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Sales Returns
    |--------------------------------------------------------------------------
    */
    'sales_returns' => [
        'created' => 'Sales return created successfully.',
        'updated' => 'Sales return updated successfully.',
        'completed' => 'Sales return completed successfully.',
        'not_found' => 'Return not found.',
        'qty_exceeded' => 'Returned quantity exceeds the purchased quantity.',
        'already_completed' => 'Return has already been processed.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Cashier Shifts
    |--------------------------------------------------------------------------
    */
    'cashier_shifts' => [
        'opened' => 'Cashier shift opened successfully.',
        'closed' => 'Cashier shift closed successfully.',
        'force_closed' => 'Cashier shift was force-closed.',
        'not_found' => 'Shift not found.',
        'already_open' => 'You already have an active shift.',
        'already_closed' => 'Shift is already closed.',
        'no_active_shift' => 'There is no active shift. Open a shift first.',
        'has_held_carts' => 'There are still held transactions. Please finish them first.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Stock Operations
    |--------------------------------------------------------------------------
    */
    'stock' => [
        'opname_created' => 'Stock opname created successfully.',
        'opname_finalized' => 'Stock opname finalized successfully.',
        'opname_updated' => 'Stock opname item updated successfully.',
        'transfer_created' => 'Stock transfer created successfully.',
        'transfer_sent' => 'Stock transfer sent successfully.',
        'transfer_received' => 'Stock transfer received successfully.',
        'transfer_cancelled' => 'Stock transfer cancelled successfully.',
        'mutation_logged' => 'Stock mutation recorded successfully.',
        'insufficient_stock' => 'Insufficient stock for this operation.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Purchase Orders
    |--------------------------------------------------------------------------
    */
    'purchase_orders' => [
        'created' => 'Purchase order created successfully.',
        'placed' => 'Purchase order placed successfully.',
        'cancelled' => 'Purchase order cancelled successfully.',
        'not_found' => 'Purchase order not found.',
        'cannot_cancel' => 'This purchase order cannot be cancelled in its current status.',
        'only_draft' => 'Only purchase orders with Draft status can be placed.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Goods Receiving
    |--------------------------------------------------------------------------
    */
    'goods_receivings' => [
        'created' => 'Goods receiving recorded successfully.',
        'not_found' => 'Goods receiving not found.',
        'item_not_found' => 'Item not found in the purchase order.',
        'exceeds_remaining' => 'Received quantity exceeds the remaining quantity for item :product_id.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Supplier Returns
    |--------------------------------------------------------------------------
    */
    'supplier_returns' => [
        'created' => 'Supplier return created successfully.',
        'completed' => 'Supplier return completed successfully.',
        'cancelled' => 'Supplier return cancelled successfully.',
        'not_found' => 'Supplier return not found.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Receivables
    |--------------------------------------------------------------------------
    */
    'receivables' => [
        'payment_recorded' => 'Receivable payment recorded successfully.',
        'partial_payment' => 'Partial payment successful.',
        'fully_paid' => 'Receivable fully paid.',
        'not_found' => 'Receivable not found.',
        'collection_note_updated' => 'Collection note updated successfully.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Payables
    |--------------------------------------------------------------------------
    */
    'payables' => [
        'created' => 'Supplier payable recorded successfully.',
        'payment_recorded' => 'Payable payment recorded successfully.',
        'not_found' => 'Payable not found.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Pricing Rules
    |--------------------------------------------------------------------------
    */
    'pricing_rules' => [
        'created' => 'Pricing rule created successfully.',
        'updated' => 'Pricing rule updated successfully.',
        'deleted' => 'Pricing rule deleted successfully.',
        'not_found' => 'Pricing rule not found.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Price Lists
    |--------------------------------------------------------------------------
    */
    'price_lists' => [
        'created' => 'Price list created successfully.',
        'updated' => 'Price list updated successfully.',
        'deleted' => 'Price list deleted successfully.',
        'not_found' => 'Price list not found.',
        'item_added' => 'Price list item added successfully.',
        'item_removed' => 'Price list item removed successfully.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Customer Vouchers
    |--------------------------------------------------------------------------
    */
    'vouchers' => [
        'created' => 'Voucher created successfully.',
        'updated' => 'Voucher updated successfully.',
        'deleted' => 'Voucher deleted successfully.',
        'redeemed' => 'Voucher redeemed successfully.',
        'not_found' => 'Voucher not found.',
        'expired' => 'Voucher has expired.',
        'already_used' => 'Voucher has already been used.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Customer Segments
    |--------------------------------------------------------------------------
    */
    'segments' => [
        'created' => 'Segment created successfully.',
        'updated' => 'Segment updated successfully.',
        'deleted' => 'Segment deleted successfully.',
        'member_added' => 'Customer added to segment successfully.',
        'member_removed' => 'Customer removed from segment successfully.',
        'not_found' => 'Segment not found.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - CRM Campaigns
    |--------------------------------------------------------------------------
    */
    'campaigns' => [
        'created' => 'Campaign created successfully.',
        'updated' => 'Campaign updated successfully.',
        'deleted' => 'Campaign deleted successfully.',
        'processed' => 'Campaign is being processed.',
        'cancelled' => 'Campaign cancelled successfully.',
        'mark_sent' => 'Campaign log marked as sent successfully.',
        'skip' => 'Campaign log skipped successfully.',
        'not_found' => 'Campaign not found.',
        'process_failed' => 'Campaign processing failed.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Discount Approvals
    |--------------------------------------------------------------------------
    */
    'discount_approvals' => [
        'approved' => 'Discount approved successfully.',
        'denied' => 'Discount rejected successfully.',
        'not_found' => 'Request not found.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Bank Accounts
    |--------------------------------------------------------------------------
    */
    'bank_accounts' => [
        'created' => 'Bank account added successfully.',
        'updated' => 'Bank account updated successfully.',
        'deleted' => 'Bank account deleted successfully.',
        'toggled' => 'Bank account status updated successfully.',
        'order_updated' => 'Bank account order updated successfully.',
        'not_found' => 'Bank account not found.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Warehouses
    |--------------------------------------------------------------------------
    */
    'warehouses' => [
        'created' => 'Warehouse added successfully.',
        'updated' => 'Warehouse updated successfully.',
        'deleted' => 'Warehouse deleted successfully.',
        'not_found' => 'Warehouse not found.',
        'has_transactions' => 'Warehouse cannot be deleted because it already has transactions.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Settings
    |--------------------------------------------------------------------------
    */
    'settings' => [
        'saved' => 'Settings saved successfully.',
        'payment_updated' => 'Payment settings updated successfully.',
        'store_updated' => 'Store profile updated successfully.',
        'printer_updated' => 'Printer settings saved successfully.',
        'loyalty_updated' => 'Loyalty settings saved successfully.',
        'target_updated' => 'Sales target updated successfully.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - WhatsApp
    |--------------------------------------------------------------------------
    */
    'whatsapp' => [
        'test_sent' => 'Test message sent successfully.',
        'test_failed' => 'Failed to send test message.',
        'connected' => 'WhatsApp connected successfully.',
        'disconnected' => 'WhatsApp disconnected successfully.',
        'qr_generated' => 'QR code generated successfully.',
        'start_failed' => 'Failed to start WhatsApp service.',
        'not_connected' => 'WhatsApp is not connected yet.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Users & Roles
    |--------------------------------------------------------------------------
    */
    'users' => [
        'created' => 'User created successfully.',
        'updated' => 'User updated successfully.',
        'deleted' => 'User deleted successfully.',
        'password_changed' => 'Password changed successfully.',
        'not_found' => 'User not found.',
        'cannot_delete_self' => 'You cannot delete your own account.',
    ],

    'roles' => [
        'created' => 'Role created successfully.',
        'updated' => 'Role updated successfully.',
        'deleted' => 'Role deleted successfully.',
        'not_found' => 'Role not found.',
        'cannot_delete_system' => 'System role cannot be deleted.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Audit Logs
    |--------------------------------------------------------------------------
    */
    'audit_logs' => [
        'access_denied' => 'You do not have permission to view audit logs.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash Messages - Language
    |--------------------------------------------------------------------------
    */
    'language' => [
        'changed' => 'Language changed successfully.',
        'not_found' => 'Language not found.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Messages
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'required' => 'The :attribute field is required.',
        'email' => 'The email format is invalid.',
        'min' => 'The :attribute field must be at least :min characters.',
        'max' => 'The :attribute field may not be greater than :max characters.',
        'numeric' => 'The :attribute field must be numeric.',
        'unique' => 'The :attribute has already been taken.',
        'exists' => 'The selected :attribute is invalid.',
        'date' => 'The :attribute is not a valid date.',
        'integer' => 'The :attribute field must be an integer.',
        'positive' => 'The :attribute field must be positive.',
    ],
];
