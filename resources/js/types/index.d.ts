import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    user_type?: 'admin' | 'designer' | 'client';
    [key: string]: unknown; // This allows for additional properties...
}

export interface PageProps {
    flash?: {
        success?: string;
        error?: string;
        info?: string;
        warning?: string;
    };
    [key: string]: unknown;
}

// Model interfaces
export interface Designer {
    id: number;
    user_id: number;
    store_name: string;
    store_description?: string;
    subscription_status: 'pending' | 'active' | 'expired';
    subscription_start_date?: string;
    subscription_end_date?: string;
    payment_proof_path?: string;
    earnings: number;
    paid_earnings: number;
    subscription_price?: number;
    pricing_plan_id?: number;
    created_at: string;
    updated_at: string;
    user?: User;
    pricing_plan?: PricingPlan;
    socialMedia?: DesignerSocialMedia[];
    rachmat?: Rachma[];
    rachmat_count?: number;
    rachmat_sum_sales_count?: number; // Will be replaced with orders_count
    orders_count?: number;
}

export interface DesignerSocialMedia {
    id: number;
    designer_id: number;
    platform: 'facebook' | 'instagram' | 'twitter' | 'telegram' | 'whatsapp' | 'youtube' | 'website';
    url: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    designer?: Designer;
}

export interface PricingPlan {
    id: number;
    name: string;
    duration_months: number;
    price: number;
    description?: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    formatted_price?: string;
    duration_text?: string;
}

export interface Category {
    id: number;
    name: string;
    name_ar?: string;
    name_fr?: string;
    slug: string;
    description?: string;
    sub_categories?: SubCategory[];
    rachmat_count?: number;
    created_at: string;
    updated_at: string;
}

export interface SubCategory {
    id: number;
    category_id: number;
    name: string;
    name_ar?: string;
    name_fr?: string;
    slug: string;
    description?: string;
    created_at: string;
    updated_at: string;
    category?: Category;
}

export interface PartsSuggestion {
    id: number;
    name_ar: string;
    name_fr: string;
    admin_id: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    admin?: User;
    localized_name?: string;
    name?: string;
    display_name?: string;
}

export interface RachmaFile {
    id: number;
    path: string;
    original_name: string;
    format: string;
    size?: number;
    is_primary: boolean;
    uploaded_at: string;
    description?: string;
}

export interface Rachma {
    id: number;
    designer_id: number;
    title_ar: string;
    title_fr: string;
    description_ar: string;
    description_fr: string;
    color_numbers: string[];
    price: number;
    preview_images: string[];
    files: RachmaFile[];
    file_path: string;
    average_rating: number;
    ratings_count: number;
    orders_count?: number;
    orders_sum_amount?: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface Part {
    id: number;
    rachma_id: number;
    name: string;
    name_ar?: string;
    name_fr?: string;
    length?: number;
    height?: number;
    stitches?: number;
    order: number;
    created_at: string;
    updated_at: string;
    rachma?: Rachma;
    localized_name?: string;
}

export interface OrderItem {
    id: number;
    order_id: number;
    rachma_id: number;
    price: number;
    created_at: string;
    updated_at: string;
    rachma?: Rachma;
}

export interface Order {
    id: number;
    client_id: number;
    rachma_id?: number; // Made optional for multi-item orders
    amount: number;
    payment_method: 'ccp' | 'baridi_mob' | 'dahabiya';
    payment_proof_path: string;
    payment_proof_url?: string;
    status: 'pending' | 'completed' | 'rejected';
    confirmed_at?: string;
    file_sent_at?: string;
    admin_notes?: string;
    rejection_reason?: string;
    rejected_at?: string;
    completed_at?: string;
    created_at: string;
    updated_at: string;
    client?: User;
    rachma?: Rachma; // For backward compatibility
    order_items?: OrderItem[]; // New field for multi-item orders
}

export interface Comment {
    id: number;
    user_id: number;
    target_id: number;
    target_type: 'rachma' | 'store';
    comment: string;
    created_at: string;
    updated_at: string;
    user?: User;
}

export interface Rating {
    id: number;
    user_id: number;
    target_id: number;
    target_type: 'rachma' | 'store';
    rating: number;
    comment?: string;
    created_at: string;
    updated_at: string;
    user?: User;
}

export interface AdminPaymentInfo {
    id: number;
    ccp_number?: string;
    ccp_key?: string;
    nom?: string;
    adress?: string;
    baridimob?: string;
    created_at: string;
    updated_at: string;
    formatted_ccp_number?: string;
    masked_ccp_key?: string;
}

export interface SubscriptionRequest {
    id: number;
    designer_id: number;
    pricing_plan_id: number;
    status: 'pending' | 'approved' | 'rejected';
    notes?: string;
    payment_proof_path?: string;
    payment_proof_original_name?: string;
    payment_proof_size?: number;
    payment_proof_mime_type?: string;
    admin_notes?: string;
    reviewed_by?: number;
    reviewed_at?: string;
    subscription_price: number;
    requested_start_date: string;
    created_at: string;
    updated_at: string;
    designer?: Designer;
    pricing_plan?: PricingPlan;
    reviewed_by_user?: User;
    status_label?: string;
    payment_proof_url?: string;
    formatted_file_size?: string;
    has_payment_proof?: boolean;
}

export interface PrivacyPolicy {
    id: number;
    title: string;
    content: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginated<T> {
    data: T[];
    links: PaginationLink[];
    meta: {
        current_page: number;
        from: number;
        last_page: number;
        links: PaginationLink[];
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}
