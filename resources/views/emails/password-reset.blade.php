<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f6f9; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background-color: #165b9c; padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 600;">SIMAH</h1>
                            <p style="color: rgba(255,255,255,0.85); margin: 8px 0 0; font-size: 14px;">Recuperação de Senha</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #333; margin: 0 0 20px; font-size: 22px;">Olá, {{ $userName }}</h2>

                            <p style="color: #555; font-size: 15px; line-height: 1.6; margin: 0 0 20px;">
                                Recebemos uma solicitação para redefinir a senha da sua conta. Clique no botão abaixo para criar uma nova senha:
                            </p>

                            <!-- Botão -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $resetUrl }}" style="display: inline-block; background-color: #165b9c; color: #ffffff; text-decoration: none; padding: 14px 40px; border-radius: 25px; font-size: 16px; font-weight: 600;">
                                            Redefinir Senha
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Aviso -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #ffc107; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 15px 20px;">
                                        <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.5;">
                                            Este link expira em <strong>60 minutos</strong>. Se você não solicitou esta recuperação, ignore este e-mail.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #999; font-size: 13px; line-height: 1.5; margin: 20px 0 0;">
                                Se o botão não funcionar, copie e cole o link abaixo no seu navegador:<br>
                                <a href="{{ $resetUrl }}" style="color: #165b9c; word-break: break-all;">{{ $resetUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 25px 30px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; color: #999; font-size: 12px; line-height: 1.5;">
                                Este é um e-mail automático. Por favor, não responda.
                            </p>
                            <p style="margin: 8px 0 0; color: #999; font-size: 12px;">
                                SIMAH - Sistema de Monitoramento Ambiental Hídrico
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
