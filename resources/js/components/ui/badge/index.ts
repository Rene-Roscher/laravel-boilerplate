import { cva, type VariantProps } from 'class-variance-authority'

export { default as Badge } from './Badge.vue'

export const badgeVariants = cva(
  'inline-flex items-center rounded-lg font-medium ring-1 ring-inset transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
  {
    variants: {
      variant: {
        zinc: 'bg-zinc-50 text-zinc-700 ring-zinc-900/10 dark:bg-zinc-700/5 dark:text-zinc-400 dark:ring-white/10',
        gray: 'bg-gray-50 text-gray-700 ring-gray-600/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20',
        red: 'bg-red-50 text-red-700 ring-red-600/10 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/20',
        yellow: 'bg-yellow-50 text-yellow-800 ring-yellow-600/20 dark:bg-yellow-400/10 dark:text-yellow-500 dark:ring-yellow-400/20',
        green: 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20',
        blue: 'bg-blue-50 text-blue-700 ring-blue-700/10 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/30',
        indigo: 'bg-indigo-50 text-indigo-700 ring-indigo-700/10 dark:bg-indigo-400/10 dark:text-indigo-400 dark:ring-indigo-400/30',
        purple: 'bg-purple-50 text-purple-700 ring-purple-700/10 dark:bg-purple-400/10 dark:text-purple-400 dark:ring-purple-400/30',
        pink: 'bg-pink-50 text-pink-700 ring-pink-700/10 dark:bg-pink-400/10 dark:text-pink-400 dark:ring-pink-400/20',
        transparent: 'bg-transparent text-gray-700 ring-gray-600/10 dark:bg-transparent dark:text-gray-400 dark:ring-gray-400/20',
        cyan: 'bg-cyan-50 text-cyan-700 ring-cyan-700/10 dark:bg-cyan-400/10 dark:text-cyan-400 dark:ring-cyan-400/20',
        orange: 'bg-orange-50 text-orange-700 ring-orange-700/10 dark:bg-orange-400/10 dark:text-orange-400 dark:ring-orange-400/20',
        teal: 'bg-teal-50 text-teal-700 ring-teal-700/10 dark:bg-teal-400/10 dark:text-teal-400 dark:ring-teal-400/20',
      },
      size: {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-2 py-1 text-xs',
        lg: 'px-2.5 py-1.5 text-sm',
      },
    },
    defaultVariants: {
      variant: 'gray',
      size: 'md'
    },
  },
)

export type BadgeVariants = VariantProps<typeof badgeVariants>
