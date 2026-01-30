<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Form - {{ $service_form->sku }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .page {
            padding: 20px 30px;
        }
        .page-break {
            page-break-after: always;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table {
            margin-bottom: 15px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-cell {
            width: 120px;
            padding: 5px;
        }
        .logo-cell img {
            max-width: 100px;
            height: auto;
        }
        .qms-header {
            border: 1px solid #000;
        }
        .qms-header td {
            border: 1px solid #000;
            padding: 3px 8px;
        }
        .qms-title {
            font-weight: bold;
            font-size: 11px;
            text-align: center;
            background-color: #f0f0f0;
        }
        .form-title {
            font-weight: bold;
            font-size: 16px;
            text-align: center;
        }
        .form-table {
            border: 1px solid #000;
            margin-top: 15px;
        }
        .form-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }
        .form-label {
            width: 35%;
            font-weight: normal;
        }
        .form-value {
            width: 65%;
        }
        .checklist-label {
            color: #0066cc;
            padding-left: 20px;
        }
        .address-cell {
            height: 60px;
        }
        .nature-cell {
            height: 40px;
        }
        .footer-text {
            text-align: center;
            font-size: 11px;
            margin-top: 10px;
        }
        .ack-section {
            height: 80px;
        }
        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            margin-right: 5px;
            text-align: center;
            line-height: 10px;
            font-size: 10px;
            font-family: DejaVu Sans, sans-serif;
        }
        /* Page 2 Styles */
        .checklist-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: underline;
            margin-bottom: 15px;
        }
        .section-title {
            font-weight: bold;
            margin: 12px 0 5px 0;
        }
        .checklist-item {
            margin: 3px 0 3px 15px;
        }
        .specific-lines {
            border-bottom: 1px solid #000;
            margin: 5px 0;
            height: 18px;
        }
    </style>
</head>
<body>
    <!-- Page 1: Service Requisition Form -->
    <div class="page page-break">
        <!-- Header with Logo and QMS Info -->
        <table class="header-table">
            <tr>
                <td class="logo-cell" rowspan="5" style="border: 1px solid #000;">
                    <img src="{{ public_path('images/imax.jpg') }}" alt="Logo" style="width: 100px;">
                </td>
                <td colspan="3" class="qms-title" style="border: 1px solid #000;">QUALITY MANAGEMENT SYSTEM</td>
            </tr>
            <tr>
                <td style="font-size: 10px; width: 50%;">Title</td>
                <td style="border-left: 1px solid #000; font-size: 10px;">Document No.</td>
                <td style="border-left: 1px solid #000; border-right: 1px solid #000; font-size: 10px;">Revision</td>
            </tr>
            <tr>
                <td class="form-title" rowspan="3" style="border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: 1px solid #000;">SERVICE REQUISITION FORM</td>
                <td style="border-left: 1px solid #000; font-size: 10px; text-align: center;">PC-PRO-P07/F02</td>
                <td style="border-left: 1px solid #000; border-right: 1px solid #000; font-size: 10px; text-align: center;">2</td>
            </tr>
            <tr>
                <td colspan="2" style="border-left: 1px solid #000; border-right: 1px solid #000; border-top: 1px solid #000; font-size: 10px;">Date Effective</td>
            </tr>
            <tr>
                <td colspan="2" style="border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px; text-align: center;">{{ now()->format('d/m/Y') }}</td>
            </tr>
        </table>

        <!-- Form Content -->
        <table class="form-table">
            <!-- Date / Ref. No. / Ticket No. -->
            <tr>
                <td class="form-label">Date / Ref. No. / <span style="color: #0066cc;">Ticket No.</span></td>
                <td class="form-value">
                    {{ $service_form->date ? $service_form->date->format('d/m/Y') : '' }} / {{ $service_form->sku ?? '' }}
                </td>
            </tr>
            <!-- Customer Name -->
            <tr>
                <td class="form-label">Customers Name</td>
                <td class="form-value">{{ $customer_name }}</td>
            </tr>
            <!-- Address -->
            <tr>
                <td class="form-label">Address</td>
                <td class="form-value address-cell">{{ $address }}</td>
            </tr>
            <!-- Contact No. -->
            <tr>
                <td class="form-label">Contact No.</td>
                <td class="form-value">{{ $service_form->contact_no ?? '' }}</td>
            </tr>
            <!-- Contact Person -->
            <tr>
                <td class="form-label">Contact Person</td>
                <td class="form-value">{{ $service_form->contact_person ?? '' }}</td>
            </tr>
            <!-- Model No. / Serial No. -->
            <tr>
                <td class="form-label">Model No. / <span style="color: #0066cc;">Serial No.</span></td>
                <td class="form-value">{{ $service_form->model_no ?? '' }} / {{ $service_form->serial_no ?? '' }}</td>
            </tr>
            <!-- Invoice No./Date -->
            <tr>
                <td class="form-label">Invoice No./Date</td>
                <td class="form-value">
                    {{ $service_form->invoice_no ?? '' }}
                    @if($service_form->invoice_date)
                        / {{ $service_form->invoice_date->format('d/m/Y') }}
                    @endif
                </td>
            </tr>
            <!-- Warranty Status -->
            <tr>
                <td class="form-label">Warranty status</td>
                <td class="form-value">{{ $warranty_status }}</td>
            </tr>
            <!-- Dealer's Name / Contact No. -->
            <tr>
                <td class="form-label">Dealer's Name / Contact No.</td>
                <td class="form-value">
                    {{ $service_form->dealer_name ?? '' }}
                    @if($service_form->dealer_contact_no)
                        / {{ $service_form->dealer_contact_no }}
                    @endif
                </td>
            </tr>
            <!-- Nature of Problem -->
            <tr>
                <td class="form-label">Nature of Problem</td>
                <td class="form-value nature-cell">{{ $service_form->nature_of_problem ?? '' }}</td>
            </tr>
            <!-- Date to Attend -->
            <tr>
                <td class="form-label">Date to Attend</td>
                <td class="form-value">{{ $service_form->date_to_attend ? $service_form->date_to_attend->format('d/m/Y') : '' }}</td>
            </tr>
            <!-- Report Checklist Header -->
            <tr>
                <td class="form-label">Report (Checklist items:)</td>
                <td class="form-value"></td>
            </tr>
            <!-- Checklist Items -->
            @foreach($checklist_items as $key => $label)
                @php
                    $checklist_data = $service_form->report_checklist[$key] ?? null;
                    $is_checked = $checklist_data['checked'] ?? false;
                    $remark = $checklist_data['remark'] ?? '';
                @endphp
                <tr>
                    <td class="form-label checklist-label">
                        <span class="checkbox">{{ $is_checked ? '✓' : '' }}</span>
                        {{ $label }}
                    </td>
                    <td class="form-value">{{ $remark }}</td>
                </tr>
            @endforeach
            <!-- Technician -->
            <tr>
                <td class="form-label">Technician</td>
                <td class="form-value">{{ $technician_name }}</td>
            </tr>
            <!-- Customer Acknowledgement -->
            <tr>
                <td class="form-label">Customer Acknowledgement</td>
                <td class="form-value ack-section">
                    <div style="margin-top: 10px;">Name of signatory /Company Chop :</div>
                    <div style="margin-top: 25px;">Date : _________________</div>
                    <div style="margin-top: 5px;">*Service start from __________ to __________ (time)</div>
                </td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer-text">
            Power Cool Equipments (M) Sdn Bhd (383045-D)
        </div>
    </div>

    <!-- Page 2: Service Check List -->
    <div class="page">
        <h1 class="checklist-title">Service Check List</h1>

        <!-- 1. Pre-Service -->
        <div class="section-title">1. Pre-Service</div>
        <div class="checklist-item"><span class="checkbox"></span> Verify model, serial number, and manufacturer specs</div>
        <div class="checklist-item"><span class="checkbox"></span> Review customer complaint or service request</div>

        <!-- 2. Site & Environment Check -->
        <div class="section-title">2. Site & Environment Check</div>
        <div class="checklist-item"><span class="checkbox"></span> Ventilation/clearance (per manufacturer specs)</div>
        <div class="checklist-item"><span class="checkbox"></span> No direct sunlight overheating cabinet</div>
        <div class="checklist-item"><span class="checkbox"></span> Level installation (front-to-back and side-to-side)</div>

        <!-- 3. Electrical Inspection -->
        <div class="section-title">3. Electrical Inspection</div>
        <div class="checklist-item"><span class="checkbox"></span> Proper grounding</div>
        <div class="checklist-item"><span class="checkbox"></span> Plug point and plug top free of damage</div>
        <div class="checklist-item"><span class="checkbox"></span> Digital controller board wiring connections secure and correct</div>
        <div class="checklist-item"><span class="checkbox"></span> System wiring connections secure</div>

        <!-- 4. Cabinet & Door Inspection -->
        <div class="section-title">4. Cabinet & Door Inspection</div>
        <div class="checklist-item"><span class="checkbox"></span> Door gasket, door closes and self-seals properly</div>
        <div class="checklist-item"><span class="checkbox"></span> Blower plate clear (no standing water), flexible hose</div>
        <div class="checklist-item"><span class="checkbox"></span> Door hinges, door spring</div>
        <div class="checklist-item"><span class="checkbox"></span> Back water tray, L heater(if applicable)</div>

        <!-- 5. Cooling System Check -->
        <div class="section-title">5. Cooling System Check</div>
        <div class="checklist-item"><span class="checkbox"></span> Compressor starts and runs smoothly</div>
        <div class="checklist-item"><span class="checkbox"></span> No abnormal noise or vibration</div>
        <div class="checklist-item"><span class="checkbox"></span> Condenser fan motor</div>
        <div class="checklist-item"><span class="checkbox"></span> Blower fan motor/ video fan</div>
        <div class="checklist-item"><span class="checkbox"></span> Condenser coil clean (free of dust, grease, debris)</div>
        <div class="checklist-item"><span class="checkbox"></span> Cooling coil free of excessive frost/ice</div>
        <div class="checklist-item"><span class="checkbox"></span> Gas volume (gas leak,gas block)</div>
        <table style="width: 100%; margin-left: 15px; margin-top: 5px;">
            <tr>
                <td style="width: 80px; vertical-align: top;">Specific :</td>
                <td>
                    <div class="specific-lines"></div>
                    <div class="specific-lines"></div>
                    <div class="specific-lines"></div>
                </td>
            </tr>
        </table>

        <!-- 6. Temperature & Performance -->
        <div class="section-title">6. Temperature & Performance</div>
        <div class="checklist-item"><span class="checkbox"></span> Temperature setting, digital controller programmed correctly</div>
        <div class="checklist-item"><span class="checkbox"></span> Actual internal temperature verified with thermometer</div>
        <div class="checklist-item"><span class="checkbox"></span> Thermostat or sensor functioning properly</div>

        <!-- 7. Defrost & Drain System -->
        <div class="section-title">7. Defrost & Drain System</div>
        <div class="checklist-item"><span class="checkbox"></span> Defrost cycle initiates properly</div>
        <div class="checklist-item"><span class="checkbox"></span> Defrost heater or solenoid valve functioning properly</div>
        <div class="checklist-item"><span class="checkbox"></span> No water leaks inside or outside unit</div>

        <!-- 8. Final Inspection & Cleanup -->
        <div class="section-title">8. Final Inspection & Cleanup</div>
        <div class="checklist-item"><span class="checkbox"></span> Unit clean (inside and out)</div>
        <div class="checklist-item"><span class="checkbox"></span> No tools or debris left behind</div>
        <div class="checklist-item"><span class="checkbox"></span> Final operational check complete</div>

        <!-- 9. Customer Handover -->
        <div class="section-title">9. Customer Handover</div>
        <div class="checklist-item"><span class="checkbox"></span> Customer/PIC check</div>
        <div class="checklist-item"><span class="checkbox"></span> Document service details and obtain customer sign-off</div>
    </div>
</body>
</html>
