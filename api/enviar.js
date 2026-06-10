import nodemailer from 'nodemailer';

export default async function handler(req, res) {
    // CORS headers just in case
    res.setHeader('Access-Control-Allow-Credentials', true);
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'OPTIONS,POST');
    res.setHeader(
        'Access-Control-Allow-Headers',
        'X-CSRF-Token, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5, Content-Type, Date, X-Api-Version'
    );

    if (req.method === 'OPTIONS') {
        res.status(200).end();
        return;
    }

    if (req.method !== 'POST') {
        return res.status(405).json({ message: 'Método no permitido' });
    }

    const { nombre, email, telefono, mensaje, origen } = req.body;

    if (!nombre || !email || !mensaje) {
        return res.status(400).json({ message: 'Por favor, completa todos los campos obligatorios.' });
    }

    try {
        const transporter = nodemailer.createTransport({
            host: process.env.SMTP_HOST,
            port: process.env.SMTP_PORT || 465,
            secure: process.env.SMTP_PORT == 465, // true for 465, false for other ports
            auth: {
                user: process.env.SMTP_USER,
                pass: process.env.SMTP_PASS
            }
        });

        const mailOptions = {
            from: process.env.SMTP_USER,
            to: process.env.SMTP_USER, // The admin receives the email
            replyTo: email,
            subject: origen || 'Formulario Web Nuevo Contacto',
            text: `Has recibido un nuevo mensaje desde el sitio web.\n\nOrigen: ${origen || 'Formulario Web'}\nNombre: ${nombre}\nEmail: ${email}\nTeléfono: ${telefono || 'No indicado'}\n\nMensaje:\n${mensaje}`
        };

        await transporter.sendMail(mailOptions);
        
        return res.status(200).json({ status: 'success', message: '¡Gracias por tu mensaje! Nos pondremos en contacto pronto.' });
    } catch (error) {
        console.error('Error enviando email SMTP:', error);
        return res.status(500).json({ status: 'error', message: 'Oops! Hubo un error al enviar tu mensaje. Verifica la configuración SMTP en Vercel.' });
    }
}
