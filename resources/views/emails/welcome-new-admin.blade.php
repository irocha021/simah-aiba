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
                            <p style="color: rgba(255,255,255,0.85); margin: 8px 0 0; font-size: 14px;">Sistema de Monitoramento Ambiental Hídrico</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #333; margin: 0 0 20px; font-size: 22px;">Bem-vindo(a), {{ $user->name }}!</h2>

                            <p style="color: #555; font-size: 15px; line-height: 1.6; margin: 0 0 20px;">
                                Sua conta de administrador foi criada com sucesso na plataforma SIMAH. Abaixo estão seus dados de acesso:
                            </p>

                            <!-- Credenciais -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #165b9c; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px 25px;">
                                        <p style="margin: 0 0 10px; color: #333; font-size: 14px;">
                                            <strong>E-mail:</strong> {{ $user->email }}
                                        </p>
                                        <p style="margin: 0; color: #333; font-size: 14px;">
                                            <strong>Senha temporária:</strong> <code style="background: #e9ecef; padding: 3px 8px; border-radius: 4px; font-size: 15px; color: #165b9c;">{{ $plainPassword }}</code>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Aviso -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 15px 20px;">
                                        <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.5;">
                                            <strong>Importante:</strong> Por segurança, você deverá alterar sua senha no primeiro acesso.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Botão -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ url('/login') }}" style="display: inline-block; background-color: #165b9c; color: #ffffff; text-decoration: none; padding: 14px 40px; border-radius: 25px; font-size: 16px; font-weight: 600;">
                                            Acessar o Sistema
                                        </a>
                                    </td>
                                </tr>
                            </table>
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
