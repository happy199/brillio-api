<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-PPX01GY0R9"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());
    gtag('config', 'G-PPX01GY0R9');
</script>

@if(config('services.clarity.id'))
<!-- Microsoft Clarity -->
<script type="text/javascript">
    (function (c, l, a, r, i, t, y) {
        c[a] = c[a] || function () { (c[a].q = c[a].q || []).push(arguments) };
        t = l.createElement(r); t.async = 1; t.src = "https://www.clarity.ms/tag/" + i;
        y = l.getElementsByTagName(r)[0]; y.parentNode.insertBefore(t, y);
    })(window, document, "clarity", "script", "{{ config('services.clarity.id') }}");
</script>
@endif

@if(config('services.mixpanel.token'))
<!-- Mixpanel -->
<script type="text/javascript">
    (function (f, b) {
        if (!b.__SV) {
            var e, g, i, h; window.mixpanel = b; b._i = []; b.init = function (e, f, c) {
                function g(a, d) { var b = d.split("."); 2 == b.length && (a = a[b[0]], d = b[1]); a[d] = function () { a.push([d].concat(Array.prototype.slice.call(arguments, 0))) } } var a = b; "undefined" !== typeof c ? a = b[c] = [] : c = "mixpanel"; a.people = a.people || []; a.toString = function (a) { var d = "mixpanel"; "mixpanel" !== c && (d += "." + c); a || (d += " (stub)"); return d }; a.people.toString = function () { return a.toString(1) + ".people (stub)" }; i = "disable time_event track track_pageview track_links track_forms track_with_groups add_group set_group remove_group register register_once alias unregister identify name_tag set_config reset property_inventory profiles.set profiles.set_once profiles.unset profiles.increment profiles.append profiles.union profiles.track_withdrawal profiles.track_inheritance _i".split(" ");
                for (h = 0; h < i.length; h++)g(a, i[h]); b._i.push([e, f, c])
            }; b.__SV = 1.2; e = f.createElement("script"); e.type = "text/javascript"; e.async = !0; e.src = "undefined" !== typeof MIXPANEL_CUSTOM_LIB_URL ? MIXPANEL_CUSTOM_LIB_URL : "file:" === f.location.protocol && "//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//) ? "https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js" : "//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js"; g = f.getElementsByTagName("script")[0]; g.parentNode.insertBefore(e, g)
        }
    })(window, window.mixpanel || []);

    mixpanel.init("{{ config('services.mixpanel.token') }}", { batch_requests: true });

    @auth
    mixpanel.identify("{{ auth()->id() }}");
    mixpanel.people.set({
        "$name": "{{ auth()->user()->name }}",
        "$email": "{{ auth()->user()->email }}",
        "User Type": "{{ auth()->user()->user_type }}",
            @if (auth() -> user() -> organization_id)
        "Organization ID": "{{ auth()->user()->organization_id }}",
            "Organization Role": "{{ auth()->user()->organization_role }}",
                @endif
        });
    @endauth

    mixpanel.track_pageview();
</script>
@endif