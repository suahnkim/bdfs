const winston = require('winston');

module.exports = {
    logger: null,
    getLogger: ()=>{
        return this.logger;
    },
    
    // loglevel = none
    createLogger: (loglevel='none')=>{
        loglevel = loglevel.toLowerCase();
        if ( loglevel == 'none') {
            this.logger = winston.createLogger( {
                level: 'error',
                transports:[
                    new winston.transports.Console({
                        format: winston.format.printf(info=>`[${info.level.toUpperCase()}] ${info.message}`)
                    })
                ],
                silent: true
            })
        } else {
            this.logger = winston.createLogger( {
                level: loglevel,
                transports:[
                    new winston.transports.Console({
                        format: winston.format.printf(info=>`[${info.level.toUpperCase()}] ${info.message}`)
                    })
                ],
                silent: false
            })
        }
        return this.logger;
    }
}
