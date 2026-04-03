<?php

return [
    // Notification
    'notification.view' => 'Show the notification bell icon in the sidebar and access the notification listing page to view all system alerts',
    'notification.view_mobile_app' => 'Show mobile app task reminder notifications in the notification listing page and include them in the unread count badge',
    'notification.view_service_reminder' => 'Show service reminder notifications in the notification listing page and include them in the unread count badge',
    'notification.view_vehicle_service_reminder' => 'Show vehicle service reminder notifications in the notification listing page and include them in the unread count badge',
    'notification.view_production_completed' => 'Receive production completion notifications and show them in the notification listing page with unread count badge',

    // Approval
    'approval.view' => 'Show the Approval menu in the sidebar with the pending red dot badge, and access the approval listing page to approve or reject requests',
    'approval.production_material_transfer_request' => 'Show production material transfer and transfer-to-warehouse requests in the approval listing page',
    'approval.type_quotation' => 'Show quotation approval requests in the approval listing page and the Quotation option in the type filter dropdown',
    'approval.type_sale_order' => 'Show sale order approval requests in the approval listing page and the Sale Order option in the type filter dropdown',
    'approval.type_delivery_order' => 'Show delivery order approval requests in the approval listing page and the Delivery Order option in the type filter dropdown',
    'approval.type_customer' => 'Show debtor registration, credit term change, and deletion requests in the approval listing page and the Customer option in the type filter dropdown',
    'approval.type_payment_record' => 'Show payment edit and deletion requests in the approval listing page and the Payment Record option in the type filter dropdown',
    'approval.type_raw_material_request' => 'Show raw material request cancellation requests in the approval listing page and the Raw Material Request option in the type filter dropdown',
    'approval.type_complete_production_request' => 'Show complete production requests in the approval listing page and the Complete Production Request option in the type filter dropdown',
    'approval.type_grn' => 'Show GRN approval requests in the approval listing page and the GRN option in the type filter dropdown',

    // Dashboard
    'dashboard.view' => 'Show the Dashboard menu in the sidebar and access the dashboard page with sales, production, and task overview charts',

    // Inventory
    'inventory.summary.view' => 'Show the Summary sub-menu under Inventory and access the inventory summary page showing stock levels across warehouses',
    'inventory.view_action' => 'Show the Stock In, Stock Out, Transfer, and To Warehouse action buttons on the product and raw material detail pages',
    'inventory.category.view' => 'Show the Product Category menu and access the product category listing page',
    'inventory.category.create' => 'Show the New button on the product category listing page to add new categories',
    'inventory.category.edit' => 'Show the Edit button on each row in the product category listing page',
    'inventory.category.delete' => 'Show the Delete button on each row in the product category listing page',
    'inventory.product.view' => 'Show the Finish Good sub-menu under Inventory and access the product catalog listing page with stock details',
    'inventory.product.create' => 'Show the New button on the product listing page to add new products with variants and pricing',
    'inventory.product.edit' => 'Show the Edit button on each row in the product listing page to modify product info and pricing',
    'inventory.product.delete' => 'Show the Delete button on each row in the product listing page',
    'inventory.raw_material.view' => 'Show the Raw Material sub-menu under Inventory and access the raw material listing page',
    'inventory.raw_material.create' => 'Show the New button on the raw material listing page to register new raw materials',
    'inventory.raw_material.edit' => 'Show the Edit button on each row in the raw material listing page',
    'inventory.raw_material.delete' => 'Show the Delete button on each row in the raw material listing page',
    'inventory.customize.view' => 'Show the Customize sub-menu under Inventory and access the customize product listing page',
    'inventory.customize.edit' => 'Show the Edit button on each row in the customize product listing page and the Edit Customize Product button on R&D production rows',
    'inventory.raw_material_request.view' => 'Show the Raw Material Request sub-menu under Inventory and access the raw material request listing page',
    'inventory.raw_material_request.create' => 'Show the New button on the raw material request listing page to create new raw material requests',
    'inventory.raw_material_request.complete' => 'Show the Complete (green checkmark) and Cancel buttons on each pending/in-progress raw material request row',

    // Ad-hoc Service
    'adhoc_service.view' => 'Show the Ad-hoc Services menu under Inventory and access the ad-hoc service job listing page',
    'adhoc_service.create' => 'Show the New button on the ad-hoc service listing page to log new service jobs',
    'adhoc_service.edit' => 'Show the Edit button on each row in the ad-hoc service listing page',
    'adhoc_service.delete' => 'Show the Delete button on each row in the ad-hoc service listing page',

    // GRN
    'grn.view' => 'Show the GRN menu under Inventory and access the goods received note listing page',
    'grn.create' => 'Show the New and Sync to Autocount buttons on the GRN listing page, and access the create and edit forms',
    'grn.edit' => 'Update goods received note details',
    'grn.cancel' => 'Show the Cancel button on GRN rows to void a goods received note',
    'grn.delete' => 'Show the Delete button on GRN rows to soft-delete a goods received note',

    // Service Reminder
    'service_reminder.view' => 'Show the Service Reminder menu under After Service and access the service reminder listing page',
    'service_reminder.create' => 'Show the New button on the service reminder listing page to set up new reminders for customers',
    'service_reminder.edit' => 'Modify service reminder schedules and details',
    'service_reminder.receive_reminder' => 'Receive service due date reminder notifications via the scheduled cron job',

    // Service History
    'service_history.view' => 'Show the Service History menu under After Service and access the past service records listing page',
    'service_history.create' => 'Show the New button on the service history listing page to log completed service entries',

    // Warranty
    'warranty.view' => 'Show the Warranty menu under After Service and access the warranty records listing page',
    'warranty.create' => 'Access the warranty claim creation form to register new warranty claims',

    // Service Form
    'service_form.view' => 'Show the Service Form menu in the sidebar and access the service form template listing page',
    'service_form.create' => 'Show the New button on the service form listing page to design new form templates',
    'service_form.edit' => 'Show the Edit button on each row in the service form listing page',
    'service_form.delete' => 'Show the Delete button on each row in the service form listing page',

    // Sale Enquiry
    'sale_enquiry.view' => 'Show the Sale Enquiry menu in the sidebar and show the Export Excel button to download enquiry data',
    'sale_enquiry.create' => 'Show the New button on the sale enquiry listing page to log new customer enquiries',
    'sale_enquiry.edit' => 'Show the Edit button on each row in the sale enquiry listing page',
    'sale_enquiry.delete' => 'Show the Delete button on each row in the sale enquiry listing page',

    // Sales - Quotation
    'sale.quotation.view' => 'Show the Quotation menu under Sale & Invoice and access the quotation listing page with details and PDF downloads',
    'sale.quotation.create' => 'Show the New button on the quotation listing page to draft new quotations for customers',
    'sale.quotation.edit' => 'Show the Edit button on each owned quotation row in the listing page',
    'sale.quotation.view_record' => 'Show the View Record button on quotation rows to see revision and activity history for cancelled, converted, or pending approval quotations',
    'sale.quotation.delete' => 'Allow deletion of draft quotation records via the route',
    'sale.quotation.convert' => 'Show the Convert to Sale Order button on the quotation listing page to generate a sale order from approved quotations',

    // Sales - Sale Order
    'sale.sale_order.view' => 'Show the Sale Order menu under Sale & Invoice and access the sale order listing page with details',
    'sale.sale_order.create' => 'Allow creation of new sale orders directly (currently disabled in UI)',
    'sale.sale_order.edit' => 'Show the Edit button on each sale order row and access the edit form to modify items, pricing, and payment details',
    'sale.sale_order.view_record' => 'Show the View Record button on sale order rows to see revision and activity history',
    'sale.sale_order.cancel' => 'Show the Cancel button on active sale order rows and allow transfer-back of sale orders',
    'sale.sale_order.delete' => 'Show the Delete button on pending and e-order sale order rows',
    'sale.sale_order.convert_from' => 'Show the Convert From Quotation button on the sale order listing page to generate a sale order from approved quotations',
    'sale.sale_order.convert_to' => 'Show the Convert to Delivery Order button on the sale order listing page',

    // Sales - Cash Sale
    'sale.cash_sale.view' => 'Show the Cash Sale menu under Sale & Invoice and access the walk-in cash sale listing page',
    'sale.cash_sale.create' => 'Show the New button on the cash sale listing page to record new walk-in cash sales',

    // Sales - Delivery Order
    'sale.delivery_order.view' => 'Show the Delivery Order menu under Sale & Invoice and access the delivery order listing page with details',
    'sale.delivery_order.convert' => 'Access the delivery order to invoice conversion page to generate invoices from delivery orders',

    // Sales - Transport Acknowledgement
    'sale.transport_acknowledgement.view' => 'Show the Transport Acknowledgement menu under Sale & Invoice and access the transport acknowledgement listing page',
    'sale.transport_acknowledgement.create' => 'Show the Create New button on the transport acknowledgement listing page to issue new transport acknowledgements',

    // Sales - Invoice
    'sale.invoice.view' => 'Show the Invoice menu under Sale & Invoice and access the invoice listing page, including draft e-invoices, e-invoices, credit notes, and debit notes',
    'sale.invoice.convert_from_so' => 'Show the Convert From Sale Order button on the invoice listing page to generate invoices from approved sale orders',
    'sale.invoice.sync_to_autocount' => 'Show the Sync to Autocount button on the invoice listing page to push selected invoice data to AutoCount accounting system',
    'sale.invoice.convert_to_billing' => 'Show the Convert to Billing button on the invoice listing page to generate billing documents from selected invoices',
    'sale.invoice.submit_draft_e_invoice' => 'Show the Submit to Approval button on the invoice listing page to send selected invoices as draft e-invoices to MyInvois',
    'sale.invoice.submit_consolidated_e_invoice' => 'Allow submission of consolidated e-invoices to MyInvois system',

    // Sales - E-Invoice
    'sale.draft_e_invoice.view' => 'Show the Draft E-Invoice sub-link under the E-Invoice sidebar section to access draft e-invoices pending submission',
    'sale.e_invoice.view' => 'Show the E-Invoice sidebar section and access submitted e-invoices, consolidated e-invoices, and related records from MyInvois',

    // Sales - Target
    'sale.target.view' => 'Show the Target menu in the sidebar and access the sales target listing page and sale cancellation records',
    'sale.target.create' => 'Show the New button and the Duplicate button on each row in the sales target listing page',
    'sale.target.edit' => 'Show the Edit button on each row in the sales target listing page to adjust target values',

    // Sales - Billing
    'sale.billing.view' => 'Show the Billing menu in the sidebar and access the billing documents listing page with payment status',

    // Sales - Invoice Return
    'sale.invoice_return.view' => 'Show the Invoice Return menu in the sidebar and access the invoice return listing page to view and process returns',
    'sale.invoice_return.return' => 'Process product returns against invoices via the return workflow',

    // Tasks
    'task_driver.view' => 'Show the Task Driver menu under Tasks and access the driver delivery task listing page',
    'task_driver.create' => 'Show the New button on the driver task listing page to assign new tasks to drivers',
    'task_driver.edit' => 'Show the Edit button on each row in the driver task listing page',
    'task_driver.delete' => 'Show the Delete button on each row in the driver task listing page',
    'task_technician.view' => 'Show the Task Technician menu under Tasks and access the technician service task listing page',
    'task_technician.create' => 'Show the New button on the technician task listing page to assign new tasks to technicians',
    'task_technician.edit' => 'Show the Edit button on each row in the technician task listing page',
    'task_technician.delete' => 'Show the Delete button on each row in the technician task listing page',
    'task_sale.view' => 'Show the Task Sale menu under Tasks and access the salesperson task listing page',
    'task_sale.create' => 'Show the New button on the salesperson task listing page to assign new tasks to salespersons',
    'task_sale.edit' => 'Show the Edit button on each row in the salesperson task listing page',
    'task_sale.delete' => 'Show the Delete button on each row in the salesperson task listing page',

    // Production
    'production.view' => 'Show the Production menu under Production and access the production order listing page with milestone tracking',
    'production.create' => 'Show the New and Duplicate buttons on the production listing page to create new production orders',
    'production.edit' => 'Show the Edit button on each to-do production order row in the listing page',
    'production.delete' => 'Show the Delete button on each row in the production listing page',
    'production.export_excel' => 'Show the Export Excel button on the production listing page to download production data as a spreadsheet',
    'production_material.view' => 'Show the Production Material menu under Production and access production material stock and production finish good pages',
    'production_request.view' => 'Show the Production Request menu under Production and access the production request listing page',
    'production_request.create' => 'Show the New button on the production request listing page to submit new material requests for production',
    'production_request.complete' => 'Show the Complete and Cancel buttons on each production request row to mark it as completed or submit cancellation',
    'production.cancel' => 'Show the Cancel Production button on the in-progress production view page',
    'production.complete' => 'Show the Complete Task button on the in-progress production view page',

    // Ticket
    'ticket.view' => 'Show the Ticket menu under Tasks and access the customer support ticket listing page',
    'ticket.create' => 'Show the New button on the ticket listing page to open new support tickets',
    'ticket.edit' => 'Show the Edit button on each row in the ticket listing page',
    'ticket.delete' => 'Show the Delete button on each row in the ticket listing page',

    // Customer
    'customer.view' => 'Show the Debtor menu under Contacts and access the customer/debtor directory listing page',
    'customer.create' => 'Show the New and Duplicate buttons on the debtor listing page to register new customers and debtors',
    'customer.edit' => 'Show the Edit button on each row in the debtor listing page to update customer details, credit terms, and agents',
    'customer.delete' => 'Show the Delete button on each row in the debtor listing page',

    // Supplier
    'supplier.view' => 'Show the Supplier menu under Contacts and access the supplier directory listing page',
    'supplier.create' => 'Show the New button on the supplier listing page to register new suppliers',
    'supplier.edit' => 'Show the Edit button on each row in the supplier listing page',
    'supplier.delete' => 'Show the Delete button on each row in the supplier listing page',

    // Dealer
    'dealer.view' => 'Show the Dealer menu under Contacts and access the dealer directory listing page',
    'dealer.edit' => 'Show the Edit button on each row in the dealer listing page',
    'dealer.delete' => 'Show the Delete button on each row in the dealer listing page',

    // Agent Debtor
    'agent_debtor.view' => 'Show the Agent Debtor menu under Contacts and access the agent-debtor assignment listing page',
    'agent_debtor.edit' => 'Access the agent-debtor assignment edit form to update agent-debtor relationships',
    'agent_debtor.delete' => 'Remove agent-debtor assignment records',

    // Vehicle
    'vehicle.view' => 'Show the Vehicle accordion in the sidebar with Vehicle and Vehicle Service sub-links (also requires setting.view)',
    'vehicle.create' => 'Show the New button on the vehicle and vehicle service listing pages to register new entries',
    'vehicle.edit' => 'Show the Edit button on each row in the vehicle and vehicle service listing pages',
    'vehicle_service.reminder' => 'Receive vehicle maintenance due date reminder notifications via the scheduled cron job',

    // E-Order
    'e_order.view' => 'Show the E-Order Assign menu under Sale and access the pending platform orders page for Lazada, Shopee, TikTok, and WooCommerce order assignment',

    // Report
    'report.production' => 'Show the Production Report sub-menu under Report in the sidebar and access the production report page',
    'report.sales' => 'Show the Sales Report sub-menu under Report in the sidebar and access the sales report page',
    'report.stock' => 'Show the Stock Report sub-menu under Report in the sidebar and access the stock report page',
    'report.earning' => 'Show the Earning Report sub-menu under Report in the sidebar and access the earning report page',
    'report.service' => 'Show the Service Report sub-menu under Report in the sidebar and access the service report page',
    'report.technician_stock' => 'Show the Technician Stock Report sub-menu under Report in the sidebar and access the technician stock report page',

    // User & Role Management
    'user_role_management.view' => 'Show the User Management and Role Management menus in the sidebar and access user account and role listing pages',
    'user_role_management.create' => 'Show the New button on user and role listing pages to add new user accounts and roles',
    'user_role_management.edit' => 'Show the Edit button on each row in user and role listing pages to modify accounts and role permissions',
    'user_role_management.delete' => 'Show the Delete button on each row in user and role listing pages',

    // Setting
    'setting.area.view' => 'Show the City menu under Setting and access the city/area listing page',
    'setting.material_use.view' => 'Show the B.O.M Material Use menu under Setting and access the material use listing page',
    'setting.country.view' => 'Show the Country menu under Setting and access the country listing page',
    'setting.credit_term.view' => 'Show the Credit Term menu under Setting and access the credit term listing page',
    'setting.currency.view' => 'Show the Currency menu under Setting and access the currency listing page',
    'setting.debtor_type.view' => 'Show the Debtor Type menu under Setting and access the debtor type listing page',
    'setting.factory.view' => 'Show the Factory menu under Setting and access the factory listing page',
    'setting.milestone.view' => 'Show the Milestone menu under Setting and access the milestone listing page',
    'setting.payment_method.view' => 'Show the Payment Method menu under Setting and access the payment method listing page',
    'setting.inventory_type.view' => 'Show the Product Type menu under Setting and access the product type listing page',
    'setting.promotion.view' => 'Show the Promotion menu under Setting and access the promotion listing page',
    'setting.state.view' => 'Show the State menu under Setting and access the state listing page',
    'setting.project_type.view' => 'Show the Project Type menu under Setting and access the project type listing page',
    'setting.platform.view' => 'Show the Platform menu under Setting and access the platform listing page',
    'setting.priority.view' => 'Show the Priority menu under Setting and access the priority listing page',
    'setting.sales_agent.view' => 'Show the Sales Agent menu under Setting and access the sales agent listing page',
    'setting.service.view' => 'Show the Vehicle Service menu under Setting and access the vehicle service listing page',
    'setting.tax_rate.view' => 'Show the Tax Rate menu under Setting and access the tax rate page to view and edit SST and tax code values',
    'setting.sync.view' => 'Show the Sync menu under Setting and access the sync page',
    'setting.uom.view' => 'Show the UOM menu under Setting and access the unit of measure listing page',
    'setting.warranty_period.view' => 'Show the Warranty Period menu under Setting and access the warranty period listing page',
];
