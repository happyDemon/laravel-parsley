window.ParsleyValidator
    .addValidator('after', function (value, requirement) {
        var params = requirement.split('|-|');
        if(params.length == 1)
        {
            // is valid date?
            var timestamp = Date.parse(value),
                minTs = Date.parse(params[0]);

            return isNaN(timestamp) ? false : timestamp > minTs;
        }
        else
        {
            // A format was given, use momentJS
            var timestamp = moment(value, params[1]);
            var minTs = moment(params[0], params[1]);

            // If it's a valid date
            // and the difference in milliseconds is greater than 0
            return (timestamp.isValid()) ? timestamp.isAfter(minTs) : false;
        }
    }, 32)
    .addMessage('en', 'after', 'This date should be after %s');
window.ParsleyValidator
    .addValidator('before', function (value, requirement) {
        var params = requirement.split('|-|');
        if(params.length == 1)
        {
            // is valid date?
            var timestamp = Date.parse(value),
                maxTs = Date.parse(params[0]);

            return isNaN(timestamp) ? false : timestamp < maxTs;
        }
        else
        {
            // A format was given, use momentJS
            var timestamp = moment(value, params[1]);
            var maxTs = moment(params[0], params[1]);

            // If it's a valid date
            // and the difference in milliseconds is smaller than 0
            return (timestamp.isValid()) ? timestamp.isBefore(maxTs) : false;
        }
    }, 32)
    .addMessage('en', 'before', 'This date should be before %s');
window.ParsleyValidator
    .addValidator('formatDate', function (value, requirement) {
        var d = moment(value,requirement);

        return (d == null || !d.isValid());
    }, 32)
    .addMessage('en', 'formatDate', 'This date should be in this format: "%s"');
window.ParsleyValidator
    .addValidator('inList', function (value, requirement) {
        var list = requirement.split(',');

        return list.indexOf(value) > -1;
    }, 32)
    .addMessage('en', 'inList', 'The value should be one of the following: "%s"');
window.ParsleyValidator
    .addValidator('notInList', function (value, requirement) {
        var list = requirement.split(',');

        return list.indexOf(value) != -1;
    }, 32)
    .addMessage('en', 'notInList', 'The value should not be one of the following: "%s"');
window.ParsleyValidator
    .addValidator('different', function (value, requirement) {
        return $(requirement).val() != value;
    }, 32)
    .addMessage('en', 'different', 'The value should not be the same as "%s"');