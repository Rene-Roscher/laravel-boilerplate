import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    locale: string;
    ziggy: Config & { location: string };
}

export interface User {
    id: string;
    name: string;
    email: string;
    email_verified_at: string | null;
    avatar: string | null;
    avatar_url: string | null;
    organizations: Organization[];
    current_organization: Organization
    created_at: string;
    updated_at: string;
}

export interface Organization {
    id: string;
    name: string;
    avatar_url: string | null;
    is_default: boolean;
    created_at: string;
    updated_at: string;
}

export interface OrganizationRole {
    label: string;
    description: string;
    permissions: string[];
}

export type BreadcrumbItemType = BreadcrumbItem;
