<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\User;

class CustomField {

    /**
     * Gets custom user fields
     * @param int $uid userid of the user whose profile is being viewed/edited
     * @return array
     */
    public function getViewFields($uid) {

        $user = User::get();

        $fields = \DB::table(PREFIX . 'codo_fields AS f')
                ->select('f.id', 'f.name', 'f.title', 'f.type', 'f.is_mandatory', 'f.output_format', 'f.input_type', 'f.input_length', 'hide_not_set', 'def_value', 'v.value')
                ->leftJoin(PREFIX . 'codo_fields_values AS v', function($join) use ($uid) {
                    $join->on('f.id', '=', 'v.fid');
                    $join->on('v.uid', '=', \DB::raw($uid));
                })
                ->where('show_profile', '=', 1)
                ->where('enabled', '=', 1)
                ->orderBy('weight')
                ->get();

        $roles = \DB::table(PREFIX . 'codo_fields_roles')
                ->whereIn('rid', $user->rids)
                ->lists('fid');

        $allowed_fields = array();
        foreach ($fields as $field) {

            if (!in_array($field['id'], $roles) && ($field['value'] != null || $field['hide_not_set'] == 0)) {

                if ($field['value'] == null) {

                    $field['value'] = $field['def_value'];
                }
                $format = $field['output_format'];

                foreach ($field as $key => $val) {

                    $vars['field.' . $key] = $val;
                }

                $output = strtr($format, $vars);

                $field['output'] = $output;

                $allowed_fields[] = $field;
            }
        }
        return $allowed_fields;
    }

    public function getEditFields($uid) {

        $fields = \DB::table(PREFIX . 'codo_fields AS f')
                ->select('f.id', 'f.name', 'f.title', 'f.type', 'f.show_reg', 'f.is_mandatory', 'f.output_format', 'f.show_profile', 'f.input_type', 'f.input_length', 'f.data', 'v.value')
                ->leftJoin(PREFIX . 'codo_fields_values AS v', function($join) use ($uid) {

                    $join->on('f.id', '=', 'v.fid');
                    $join->on('v.uid', '=', \DB::raw($uid));
                })
                ->where('show_profile', '=', 1)
                ->where('enabled', '=', 1)
                ->orderBy('weight')
                ->get();


        $allowed_fields = array();
        foreach ($fields as $field) {

            $options = $field['data'];

            $field['def_val'] = $field['value'] != null ?
                    $field['value'] : null;
            $field['data'] = array("options" => $this->format(explode("\n", $options)));
            $allowed_fields[] = $field;
        }
        return $allowed_fields;
    }

    public function getRegistrationFields() {

        $fields = \DB::table(PREFIX . 'codo_fields AS f')
                ->select('f.id', 'f.name', 'f.title', 'f.type', 'f.show_reg', 'f.is_mandatory', 'f.output_format', 'f.show_profile', 'f.input_type', 'f.input_length', 'f.data')
                ->where('show_reg', '=', 1)
                ->where('enabled', '=', 1)
                ->orderBy('weight')
                ->get();


        $user = User::get();


        $allowed_fields = array();
        foreach ($fields as $field) {

            $options = $field['data'];

            $field['def_val'] = isset($_POST['input_' . $field['id']]) ?
                    $_POST['input_' . $field['id']] : null;
            $field['data'] = array("options" => $this->format(explode("\n", $options)));
            $allowed_fields[] = $field;
        }
        return $allowed_fields;
    }

    private function format($values) {

        $arr = array();

        foreach ($values as $val) {

            $arr[] = trim($val);
        }

        return $arr;
    }

    /**
     * Set value of all fields during registration
     */
    public function setRegistrationFields($uid) {

        //get only required fields and not allow users to fill unwanted fields
        $fields = $this->getRegistrationFields();

        foreach ($fields as $field) {

            //is this value submitted in form
            if (isset($_POST['input_' . $field['id']])) {


                //used ternary operator because netbeans was warning about too 
                //many nested blocks and I was in a mood to remove it somehow :P
                $val = $field['type'] == 'checkbox' ?
                        $val = implode(",", $_POST['input_' . $field['id']]) : $_POST['input_' . $field['id']];

                \DB::table(PREFIX . 'codo_fields_values')
                        ->insert(array(
                            'uid' => $uid,
                            'fid' => $field['id'],
                            'value' => $val
                ));
            }
        }
    }

}
