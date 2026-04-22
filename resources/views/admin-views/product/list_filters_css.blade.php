{{-- Filter Chips CSS - Copy this to list.blade.php @push('script') section --}}
<style>
    /* ===== Filter Section Background ===== */
    .bg-gradient-light {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 0 0 12px 12px;
    }

    /* ===== Filter Icon Box ===== */
    .filter-icon-box {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .filter-icon-box i {
        color: #fff;
        font-size: 22px;
    }

    .filter-title {
        font-size: 16px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 2px;
    }

    /* ===== Reset All Button ===== */
    .reset-all-btn {
        border-radius: 8px;
        font-weight: 600;
        padding: 8px 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(222, 68, 55, 0.2);
    }

    .reset-all-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(222, 68, 55, 0.35);
    }

    /* ===== Filter Group ===== */
    .filter-group {
        margin-bottom: 1.5rem;
    }

    .filter-group-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 700;
        color: #4a5568;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-group-label i {
        font-size: 18px;
        colorገ: #667eea;
    }

    /* ===== Filter Chips Container ===== */
    .filter-chips-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    /* ===== Filter Chip ===== */
    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: #ffffff;
        border: 2px solid #e2e8f0;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        color: #4a5568;
        text-decoration: none !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .filter-chip::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(102, 126, 234, 0.1);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .filter-chip:hover::before {
        width: 300px;
        height: 300px;
    }

    .filter-chip:hover {
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        color: #667eea;
        text-decoration: none;
    }

    .filter-chip.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: #ffffff !important;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        transform: scale(1.05);
    }

    .filter-chip.active:hover {
        transform: scale(1.05) translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        color: #ffffff !important;
    }

    /* ===== Success/Warning/Danger Chips ===== */
    .filter-chip-success:not(.active):hover {
        border-color: #48bb78 !important;
        color: #48bb78 !important;
    }

    .filter-chip-success.active {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%) !important;
        border-color: #48bb78 !important;
    }

    .filter-chip-warning:not(.active):hover {
        border-color: #ed8936 !important;
        color: #ed8936 !important;
    }

    .filter-chip-warning.active {
        background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%) !important;
        border-color: #ed8936 !important;
    }

    .filter-chip-danger:not(.active):hover {
        border-color: #f56565 !important;
        color: #f56565 !important;
    }

    .filter-chip-danger.active {
        background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%) !important;
        border-color: #f56565 !important;
    }

    /* ===== Chip Elements ===== */
    .chip-icon {
        font-size: 18px;
        z-index: 1;
    }

    .chip-text {
        z-index: 1;
        position: relative;
    }

    .chip-badge {
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        z-index: 1;
    }

    .badge-success {
        background-color: rgba(72, 187, 120, 0.2);
        color: #2f855a !important;
    }

    .filter-chip.active .badge-success {
        background-color: rgba(255, 255, 255, 0.3);
        color: #ffffff !important;
    }

    .badge-warning {
        background-color: rgba(237, 137, 54, 0.2);
        color: #c05621 !important;
    }

    .filter-chip.active .badge-warning {
        background-color: rgba(255, 255, 255, 0.3);
        color: #ffffff !important;
    }

    .badge-danger {
        background-color: rgba(245, 101, 101, 0.2);
        color: #c53030 !important;
    }

    .filter-chip.active .badge-danger {
        background-color: rgba(255, 255, 255, 0.3);
        color: #ffffff !important;
    }

    /* ===== Active Filters Summary ===== */
    .active-filters-summary {
        border-top: 2px dashed #e2e8f0 !important;
        animation: fadeIn 0.4s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .active-filter-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        position: relative;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .tag-success {
        background-color: rgba(72, 187, 120, 0.15);
        color: #2f855a;
        border: 1px solid rgba(72, 187, 120, 0.3);
    }

    .tag-warning {
        background-color: rgba(237, 137, 54, 0.15);
        color: #c05621;
        border: 1px solid rgba(237, 137, 54, 0.3);
    }

    .tag-danger {
        background-color: rgba(245, 101, 101, 0.15);
        color: #c53030;
        border: 1px solid rgba(245, 101, 101, 0.3);
    }

    .remove-tag {
        margin-left: 6px;
        color: inherit;
        opacity: 0.7;
        transition: all 0.2s ease;
        cursor: pointer;
        padding: 2px;
        border-radius: 50%;
    }

    .remove-tag:hover {
        opacity: 1;
        background-color: rgba(0, 0, 0, 0.1);
        transform: rotate(90deg);
    }

    /* ===== Search Input Improvements ===== */
    .input-group-merge .input-group-text {
        background-color: #fff;
        border: 2px solid #e2e8f0;
        border-right: 0;
        border-radius: 10px 0 0 10px;
    }

    .input-group-merge .form-control {
        border: 2px solid #e2e8f0;
        border-left: 0;
        border-right: 0;
        height: 48px;
        font-size: 15px;
    }

    .input-group-merge .form-control:focus {
        border-color: #667eea;
        box-shadow: none;
    }

    .input-group-merge .input-group-append .btn {
        border-radius: 0 10px 10px 0;
        border: 2px solid #e2e8f0;
        border-left: 0;
        padding: 0 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    /* ===== Card Enhancements ===== */
    .card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    /* ===== Add Product Button ===== */
    .btn-primary.px-4 {
        padding: 12px 24px !important;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-primary.px-4:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 25px rgba(102, 126, 234, 0.4);
    }

    .btn-primary.px-4:active {
        transform: translateY(-1px);
    }

    /* ===== Stock Badges in Table ===== */
    .badge.badge-soft-success {
        background: linear-gradient(135deg, rgba(72, 187, 120, 0.15) 0%, rgba(72, 187, 120, 0.1) 100%);
        color: #2f855a;
        border: 1px solid rgba(72, 187, 120, 0.3);
        padding: 6px 12px;
        font-weight: 600;
    }

    .badge.badge-soft-warning {
        background: linear-gradient(135deg, rgba(237, 137, 54, 0.15) 0%, rgba(237, 137, 54, 0.1) 100%);
        color: #c05621;
        border: 1px solid rgba(237, 137, 54, 0.3);
        padding: 6px 12px;
        font-weight: 600;
    }

    .badge.badge-soft-danger {
        background: linear-gradient(135deg, rgba(245, 101, 101, 0.15) 0%, rgba(245, 101, 101, 0.1) 100%);
        color: #c53030;
        border: 1px solid rgba(245, 101, 101, 0.3);
        padding: 6px 12px;
        font-weight: 600;
    }

    /* ===== Border Bottom ===== */
    .border-bottom {
        border-bottom: 1px solid #e2e8f0 !important;
    }

    /* ===== Utility Classes ===== */
    .gap-2 {
        gap: 0.5rem !important;
    }

    /* ===== Responsive ===== */
    @media (max-width: 768px) {
        .filter-chips-container {
            gap: 8px;
        }

        .filter-chip {
            padding: 8px 14px;
            font-size: 13px;
        }

        .filter-icon-box {
            width: 40px;
            height: 40px;
        }

        .filter-icon-box i {
            font-size: 18px;
        }
    }
</style>