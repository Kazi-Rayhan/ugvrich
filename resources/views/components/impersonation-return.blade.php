@php($original = \App\Support\Impersonation::active() ? \App\Support\Impersonation::original() : null)

@if ($original)
    <style>
        .impersonation-return {
            position: fixed;
            right: max(20px, env(safe-area-inset-right));
            bottom: max(20px, env(safe-area-inset-bottom));
            z-index: 9999;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 9px 18px 9px 10px;
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 999px;
            background: #022251;
            box-shadow: 0 10px 30px rgba(2, 34, 81, .28), 0 2px 6px rgba(2, 34, 81, .16);
            color: #fff;
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
            text-decoration: none;
            transition: transform .18s ease, background-color .18s ease, box-shadow .18s ease;
        }

        .impersonation-return:hover {
            transform: translateY(-2px);
            background: #10366c;
            box-shadow: 0 14px 34px rgba(2, 34, 81, .32), 0 3px 8px rgba(2, 34, 81, .18);
        }

        .impersonation-return:focus-visible {
            outline: 3px solid #8eb9ff;
            outline-offset: 3px;
        }

        .impersonation-return__icon {
            display: grid;
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            place-items: center;
            border-radius: 50%;
            background: #fff;
            color: #022251;
        }

        .impersonation-return__copy {
            display: grid;
            gap: 2px;
        }

        .impersonation-return__title {
            font-size: 13px;
            font-weight: 700;
            line-height: 1.25;
        }

        .impersonation-return__detail {
            max-width: 230px;
            overflow: hidden;
            color: rgba(255, 255, 255, .68);
            font-size: 11px;
            line-height: 1.25;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media (max-width: 480px) {
            .impersonation-return {
                right: max(12px, env(safe-area-inset-right));
                bottom: max(12px, env(safe-area-inset-bottom));
            }

            .impersonation-return__detail {
                max-width: min(45vw, 190px);
            }
        }
    </style>

    <a href="{{ route('impersonate.stop') }}"
       class="impersonation-return"
       aria-label="Back to your account, {{ $original->name }}">
        <span class="impersonation-return__icon" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5"></path>
                <path d="m12 19-7-7 7-7"></path>
            </svg>
        </span>
        <span class="impersonation-return__copy">
            <span class="impersonation-return__title">Back to my account</span>
            <span class="impersonation-return__detail">Return to {{ $original->name }}</span>
        </span>
    </a>
@endif
