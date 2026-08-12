import type { ComponentPropsWithoutRef } from 'react';

type InlayLogoProps = Omit<ComponentPropsWithoutRef<'img'>, 'alt' | 'src'> & {
    label?: string;
};

export function InlayLogo({ label, ...props }: InlayLogoProps) {
    return <img alt={label ?? ''} src="/inlayphp-logo.svg" {...props} />;
}
