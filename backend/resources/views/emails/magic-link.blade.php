<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>box-box · access link</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#1B2838;font-family:'JetBrains Mono',ui-monospace,Consolas,monospace;color:#F4F0E6;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#1B2838;">
        <tr>
            <td align="center" style="padding:48px 16px;">
                <table role="presentation" width="520" cellpadding="0" cellspacing="0" style="background:#0A1F3D;border:1px solid #4A6B8A;">
                    <tr>
                        <td style="padding:32px 32px 16px;border-bottom:1px solid #4A6B8A;">
                            <div style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:#B8C8D8;">§ box-box · podium predictions</div>
                            <div style="font-size:22px;margin-top:8px;color:#F4F0E6;letter-spacing:0.02em;">Access link issued</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px;font-size:14px;line-height:1.6;color:#E8E2D2;">
                            <p style="margin:0 0 16px;">A sign-in link has been issued for this address. Click below to enter the paddock.</p>
                            <p style="margin:0 0 24px;">
                                <a href="{{ $verifyUrl }}"
                                   style="display:inline-block;padding:14px 22px;background:#E10600;color:#ffffff;text-decoration:none;font-weight:500;letter-spacing:0.08em;text-transform:uppercase;font-size:13px;border:1px solid #E10600;">
                                    Make the call →
                                </a>
                            </p>
                            <p style="margin:0 0 8px;font-size:11px;color:#B8C8D8;letter-spacing:0.08em;text-transform:uppercase;">Note ——</p>
                            <p style="margin:0;font-size:12px;color:#B8C8D8;line-height:1.6;">
                                This link expires in {{ $expiresInMinutes }} minutes and can only be used once.
                                If you didn't request it, ignore this email.
                            </p>
                            <p style="margin:24px 0 0;font-size:11px;color:#4A6B8A;word-break:break-all;">
                                {{ $verifyUrl }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 32px;border-top:1px solid #4A6B8A;font-size:10px;letter-spacing:0.1em;text-transform:uppercase;color:#4A6B8A;">
                            box-box &middot; rev 0.1 &middot; sheet 1/1
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
