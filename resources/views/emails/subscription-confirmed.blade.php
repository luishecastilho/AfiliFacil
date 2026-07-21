<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Bem-vindo ao plano {{ $planName }}</title>
    </head>
    <body style="margin:0; padding:0; background-color:#f4f4f5; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5; padding:32px 16px;">
            <tr>
                <td align="center">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px; background-color:#ffffff; border-radius:12px; overflow:hidden;">
                        <tr>
                            <td style="background-color:#0a0a0a; padding:24px 32px;">
                                <table role="presentation" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="background-color:rgba(238,77,45,0.15); border-radius:8px; width:32px; height:32px; text-align:center; vertical-align:middle;">
                                            <span style="color:#EE4D2D; font-size:18px; line-height:32px;">&#9889;</span>
                                        </td>
                                        <td style="padding-left:10px; color:#ffffff; font-size:16px; font-weight:600;">
                                            AfiliFacil
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:32px;">
                                <h1 style="margin:0 0 16px; font-size:22px; line-height:1.3; color:#171717;">
                                    Bem-vindo ao plano {{ $planName }}!
                                </h1>

                                <p style="margin:0 0 20px; font-size:15px; line-height:1.6; color:#525252;">
                                    Olá, {{ $userName }}. Sua assinatura foi confirmada com sucesso. A partir de agora você
                                    tem acesso a:
                                </p>

                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fafafa; border-radius:8px; margin-bottom:24px;">
                                    <tr>
                                        <td style="padding:16px 20px;">
                                            <p style="margin:0; font-size:14px; color:#171717;">
                                                <strong>
                                                    {{ $nfLimit === null ? 'NF-e ilimitadas por mês' : "{$nfLimit} NF-e por mês" }}
                                                </strong>
                                            </p>
                                        </td>
                                    </tr>
                                </table>

                                <table role="presentation" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="border-radius:9999px; background-color:#EE4D2D;">
                                            <a
                                                href="{{ route('dashboard') }}"
                                                style="display:inline-block; padding:12px 28px; font-size:14px; font-weight:600; color:#ffffff; text-decoration:none;"
                                            >
                                                Acessar o dashboard
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:20px 32px; border-top:1px solid #e5e5e5;">
                                <p style="margin:0; font-size:12px; color:#a3a3a3;">
                                    &copy; {{ date('Y') }} AfiliFacil. Todos os direitos reservados.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
