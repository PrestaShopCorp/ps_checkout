module.exports = {
    launch: {
        executablePath: 'google-chrome-unstable',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-gpu',
            // Uncomment to activate remote debug (like remote desktop control)
            '--remote-debugging-address=0.0.0.0',
            '--remote-debugging-port=9223',
        ]
    }
};