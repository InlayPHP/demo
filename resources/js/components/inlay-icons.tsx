import type { PanelIconProps, PanelIconRegistry } from '@inlayphp/panels-react';
import {
    Braces,
    CircleDot,
    Github,
    Home,
    Images,
    Table2,
    UserCircle,
    Users,
} from 'lucide-react';
import { InlayLogo } from '@/components/inlay-logo';

function icon(Component: typeof Home) {
    return function InlayIcon({ className }: PanelIconProps) {
        return (
            <Component
                aria-hidden="true"
                className={className}
                strokeWidth={1.8}
            />
        );
    };
}

export const inlayIcons: PanelIconRegistry = {
    brand: ({ className }) => <InlayLogo className={className} />,
    braces: icon(Braces),
    dashboard: icon(Home),
    github: icon(Github),
    home: icon(Home),
    images: icon(Images),
    photo: icon(Images),
    table: icon(Table2),
    'user-circle': icon(UserCircle),
    users: icon(Users),
    fallback: icon(CircleDot),
};
