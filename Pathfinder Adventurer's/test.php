<script data-type="text/worker">
    on( "sheet:opened", () => {
        equipment_weight();
    });

    // calculate the weight of the equipment
    on( "change:repeating_equipment:equipment_weight change:repeating_equipment:equipment_quantity remove:repeating_equipment", equipment_weight );
    function equipment_weight() {
        getAttrsRepeating("equipment", ["equipment_weight", "equipment_quantity"], (user_repeating) => {
            let sum = 0;
            Object.keys(user_repeating).forEach(id => {
                sum += user_repeating[id]["equipment_weight"] * user_repeating[id]["equipment_quantity"];
            });

            // send payload
            setAttrs({"weight": sum});
        });
    }

    // send item to deposit
    on( "clicked:repeating_equipment:equipment-to-deposit", equipment_to_deposit );
    function equipment_to_deposit(event) {
        let source = event.sourceAttribute.replace(/equipment-to-deposit$/, '');
        let target = "repeating_deposit_"+generateRowID()+"_";

        getAttrsNumber([source+"equipment_quantity", source+"equipment_weight"], (user_number) => {
            getAttrs([source+"equipment_label"], (user_string) => {
                let payload = {};
                payload[target+"deposit_label"] = user_string.equipment_label;
                payload[target+"deposit_weight"] = user_string.equipment_weight;
                payload[target+"deposit_quantity"] = user_string.equipment_quantity;

                // send payload
                setAttrs( payload );

                // remove object in equipment
                removeRepeatingRow(source.slice(0, -1));
            });
        });
    }

    // send item to inventory
    on( "clicked:repeating_deposit:deposit-to-equipment", deposit_to_equipment);
    function deposit_to_equipment(event) {
        let source = event.sourceAttribute.replace(/deposit-to-inventory$/, '');
        let target = "repeating_equipment_"+generateRowID()+"_";

        getAttrsNumber([source+"deposit_quantity", source+"deposit_weight"], (user_number) => {
            getAttrs([source+"deposit_label"], (user_string) => {
                let payload = {};
                payload[target+"equipment_label"] = user_string.deposit_label;
                payload[target+"equipment_weight"] = user_string.deposit_weight;
                payload[target+"equipment_quantity"] = user_string.deposit_quantity;

                // send payload
                setAttrs( payload );

                // remove object in deposit
                removeRepeatingRow(source.slice(0, -1));
            });
        });
    }

    // ------------------------------------------
    // sub functions
    // ------------------------------------------

    // get all attribute name and their value as getAttrs()
    // return cleaned numbers
    function getAttrsNumber(attr, callback) {
        if ( !Array.isArray(attr) ) attr = [attr];
        if ( !(callback instanceof Function) ) callback = () => {};

        getAttrs(attr, (payload) => {
            Object.keys(payload).forEach( key => { payload[key] = parseNumber(payload[key]) });
            callback(payload);
        });
    }

    // get all attributes name and their values from a repeating block ordered by Id (roll20 generated)
    // return cleaned numbers
    function getAttrsRepeating(section, fields, callback) {
        section = section.replace(/^repeating_/g, '');
        if ( !Array.isArray(fields) ) fields = [fields];
        if ( !(callback instanceof Function) ) callback = () => {};
        // get data from DOM
        // note : we work on raw data and not with initialized variables
        // because we don't know what "getAttrs" produce
        getSectionIDs(section, id => {
        // prepares attributes name for "getAttrs"
            let attributeNames = id.reduce( (m,id) => [...m, ...(fields.map(field => "repeating_"+section+'_'+id+'_'+field))],[]);
            getAttrs(attributeNames, raw => {
                let payload = {}, regex = new RegExp( "repeating_" + section + "_([^_]*)_(.*)"), match;
                Object.keys(raw).forEach( key => {
                    match = key.match(regex);
                    if ( !payload.hasOwnProperty( match[1]) ) payload[ match[1] ] = {};
                    payload[ match[1] ][ match[2] ] = parseNumber( raw[key] );
                });
                callback(payload);
            });
        });
    }

    // parse and clean numbers from roll20
    // ( checkbox from roll20 return "on" for TRUE, 0 for FALSE )
    function parseNumber(value) {
        if ( value === "" ) return null;
        return parseFloat(value) || (value === "on" ? 1 : 0);
    }
</script>

weight : <input name="attr_weight" value="0" title="">

<div>
    <fieldset class="repeating_equipment">
        <table>
            <tbody>
            <tr>
                <td>
                    <input type="text" name="attr_equipment_label" title="" />
                </td>
                <td>
                    <input type="number" name="attr_equipment_weight" title="" />
                </td>
                <td>
                    <input type="number" name="attr_equipment_quantity" title="" />
                </td>
                <td>
                    <button type="action" name="act_equipment-to-deposit">send</button>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>
<div>
    <fieldset class="repeating_deposit">
        <table>
            <tbody>
            <tr>
                <td>
                    <input type="text" name="attr_deposit_label" title="" />
                </td>
                <td>
                    <input type="number" name="attr_deposit_weight" title="" />
                </td>
                <td>
                    <input type="number" name="attr_deposit_quantity" title="" />
                </td>
                <td>
                    <button type="action" name="act_deposit-to-equipment">send</button>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>
