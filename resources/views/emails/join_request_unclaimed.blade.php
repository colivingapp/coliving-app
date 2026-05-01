<html>
<head>
<style>
    body {
        padding:10px;
        background-color:#f2f9ff!important;
        color:#bbb!important;
    }

    .svg-button {
        fill:none;
        fill-rule:evenodd;
        cursor:pointer;
        stroke: rgb(5, 255, 251);
        stroke-width: 2px;
        stroke-linejoin: round;
        stroke-miterlimit:10;
    }


    .buttons-hover a {
        border-bottom: none!important;
    }
    

    @media screen and (min-width: 600px) {
        .buttons-hover:hover .svg-button {
            fill: #006969;
        }
    }
</style>
</head>
<body style="font-family: Arial, Helvetica, Sans-Serif;background-color:#f2f9ff;color:#bbb!important;font-size: 15px;">                
    <div style="text-align:center;max-width:700px;margin:0 auto;padding:10px;color:#999!important;">
        <div style="height:20px;"></div>

        <div style="font-size:24px;font-weight:bold;color:#444;">Coliving App</div>


        <div style="height:10px;"></div>

        <div style="text-align:left;margin:15px;padding:20px;padding-top:30px;padding-bottom:30px;border:1px solid #999;background:#fff;border-radius:5px;color:#222!important;">
            Hello,
            <div style="height:15px;"></div>
            Your coliving space <a href="https://coliving.app/space/{{$uid}}" target="_blank" style="color:#00a5ff;text-decoration:underline;"><b>{{ $name }}</b></a> has a new join request!
            <div style="height:20px;"></div>
            <b>{{ $from_name }}</b> sent this message:
            <div style="height:10px;"></div>
            <div style="padding:15px;border-left:3px solid #00a5ff;background:#f7fbff;white-space:pre-wrap;">{!! nl2br(e($request_message)) !!}</div>
            <div style="height:15px;"></div>
            Contact email: <a href="mailto:{{ $from_email }}" style="color:#00a5ff;text-decoration:underline;">{{ $from_email }}</a>
            <div style="height:10px;"></div>
            Coliving App profile: <a href="{{ $from_profile_url }}" target="_blank" style="color:#00a5ff;text-decoration:underline;">{{ $from_profile_url }}</a>
            <div style="height:30px;"></div>
            <b>Claim your Coliving App profile to accept or decline the guest.</b>
            <div style="height:15px;"></div>
            Sign up at <a href="https://coliving.app" target="_blank" style="color:#00a5ff;text-decoration:underline;">https://coliving.app</a> and click on the <b>Claim</b> button in your coliving space <a href="https://coliving.app/space/{{$uid}}" target="_blank" style="color:#00a5ff;text-decoration:underline;">profile</a>.
            <div style="height:30px;"></div>
            Thank you for your attention to this matter.
        </div>

        <div style="height:10px;"></div>

        Coliving App © 2026
        
        <div style="height:35px;"></div>

    </div>
</body>
</html>