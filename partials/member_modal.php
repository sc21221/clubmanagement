<?php

    function getMemberModalCreate()
    {
        $htmlMemberModalCreate = <<<'EOT'
            <input type="hidden" name="action" value="create">
        EOT;
        return $htmlMemberModalCreate;
    }

    function getMemberModalUpdate()
    {
        $htmlMemberModalUpdate = <<<'EOT'
            <input type="hidden" name="action" value="update">
        EOT;
        return $htmlMemberModalUpdate;
    }

    function getMemberModal($fnPrefix)
    {
        $htmlMemberModal = <<<'EOT'
        <div class="modal fade" id="%%fnPrefix%%MemberModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header" style="cursor: move;" id="%%fnPrefix%%MemberModalHeader">
                        <h5 class="modal-title">%%fnTitle%%</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        %%ReplaceWithMemberModalAction%%
                        <input type="hidden" name="id" id="%%fnPrefix%%_id">
                        <div class="modal-body">
                        %%ReplaceWithMemberTabHeader%%
                        <div class="tab-content">
                                <div class="tab-pane fade show active" id="%%fnPrefix%%_details" role="tabpanel" aria-labelledby="tab-1-tab">
                                    %%ReplaceWithMemberDetails%%    
                                </div>
                                <div class="tab-pane fade" id="%%fnPrefix%%_member_address_details" role="tabpanel" aria-labelledby="tab-2-tab">
                                    %%ReplaceWithMemberAddressDetails%%
                                </div>
                                <div class="tab-pane fade" id="%%fnPrefix%%_member_bank_details" role="tabpanel" aria-labelledby="tab-3-tab">
                                    %%ReplaceWithMemberBankDetails%%
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                            <button type="submit" class="btn btn-success">Speichern</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        EOT;
        return str_replace('%%fnPrefix%%', $fnPrefix, $htmlMemberModal);
    }

    function getMemberTabHeader($fnPrefix)
    {
        $htmlMemberTabHeader = <<<'EOT'
        <ul class="nav nav-tabs" id="tabContent_%%fnPrefix%%">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-1-tab" data-bs-toggle="tab" data-bs-target="#%%fnPrefix%%_details" type="button" role="tab" aria-controls="%%fnPrefix%%_details" aria-selected="true">
                    Mitglied
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-2-tab" data-bs-toggle="tab" data-bs-target="#%%fnPrefix%%_member_address_details" type="button" role="tab" aria-controls="%%fnPrefix%%_member_address_details" aria-selected="false">
                    Adressdaten
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-3-tab" data-bs-toggle="tab" data-bs-target="#%%fnPrefix%%_member_bank_details" type="button" role="tab" aria-controls="%%fnPrefix%%_member_bank_details" aria-selected="false">
                    Bankdaten
                </button>
            </li>
        </ul>
        EOT;
        return str_replace('%%fnPrefix%%', $fnPrefix, $htmlMemberTabHeader);
    }

    function getMemberDetails($fnPrefix)
    {
        $htmlMemberDetails = <<<'EOT'
        <div class="row">
            <div class="col-md-6 mb-3 form-floating">
                <select class="form-select" id="%%fnPrefix%%_salutation" name="salutation" placeholder="Anrede">
                    <option value="Herr">Herr</option>
                    <option value="Frau">Frau</option>
                    <option value="Divers">Divers</option>
                </select>
                <label for="%%fnPrefix%%_salutation" class="form-label">Anrede *</label>
            </div>
            <div class="col-md-6 mb-3 form-floating">
                <select class="form-select" id="%%fnPrefix%%_sex" name="sex" placeholder="Geschlecht">
                    <option value="Männlich">Männlich</option>
                    <option value="Weiblich">Weiblich</option>
                    <option value="Divers">Divers</option>
                </select>
                <label for="%%fnPrefix%%_sex" class="form-label">Geschlecht *</label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3 form-floating">
                <input type="text" class="form-control" id="%%fnPrefix%%_first_name" name="first_name" required placeholder="Vorname">
                <label for="%%fnPrefix%%_first_name" class="form-label">Vorname *</label>
            </div>
            <div class="col-md-6 mb-3 form-floating">
                <input type="text" class="form-control" id="%%fnPrefix%%_last_name" name="last_name" required placeholder="Nachname">
                <label for="%%fnPrefix%%_last_name" class="form-label">Nachname *</label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3 form-floating">
                <input type="email" class="form-control" id="%%fnPrefix%%_email" name="email" required placeholder="E-Mail">
                <label for="%%fnPrefix%%_email" class="form-label">E-Mail *</label>
            </div>
            <div class="col-md-6 mb-3 form-floating">
                <input type="tel" class="form-control" id="%%fnPrefix%%_phone" name="phone" placeholder="Telefon">
                <label for="%%fnPrefix%%_phone" class="form-label">Telefon</label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3 form-floating">
                <input type="date" class="form-control" id="%%fnPrefix%%_join_date" name="join_date" required placeholder="Beitrittsdatum"      >
                <label for="%%fnPrefix%%_join_date" class="form-label">Beitrittsdatum *</label>
            </div>
            <div class="col-md-6 mb-3 form-floating">
                <input type="date" class="form-control" id="%%fnPrefix%%_birth_date" name="birth_date" placeholder="Geburtsdatum">
                <label for="%%fnPrefix%%_birth_date" class="form-label">Geburtsdatum</label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3 form-floating">
                <select class="form-select" id="%%fnPrefix%%_membership_type" name="membership_type" placeholder="Mitgliedstyp">
                    <option value="Vollmitglied">Vollmitglied</option>
                    <option value="Fördermitglied">Fördermitglied</option>
                    <option value="Jugendmitglied">Jugendmitglied</option>
                    <option value="Ehrenmitglied">Ehrenmitglied</option>
                </select>
                <label for="%%fnPrefix%%_membership_type" class="form-label">Mitgliedstyp</label>
            </div>
            <div class="col-md-6 mb-3 form-floating">
                <select class="form-select" id="%%fnPrefix%%_status" name="status" placeholder="Status">
                    <option value="Aktiv">Aktiv</option>
                    <option value="Inaktiv">Inaktiv</option>
                    <option value="Gesperrt">Gesperrt</option>
                </select>
                <label for="%%fnPrefix%%_status" class="form-label">Status</label>
            </div>
        </div>
        <div class="form-floating mb-3">
            <input type="date" class="form-control" id="%%fnPrefix%%_leave_date " name="leave_date" placeholder="Austrittsdatum"></input>
            <label for="%%fnPrefix%%_leave_date" class="form-label">Austrittsdatum</label>
        </div>
        EOT;
        return str_replace('%%fnPrefix%%', $fnPrefix, $htmlMemberDetails);
    }

    function getMemberAddressDetails($fnPrefix)
    {
        $htmlMemberAddressDetails = <<<'EOT'
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="%%fnPrefix%%_street" name="street" placeholder="Straße"></input>
            <label for="%%fnPrefix%%_address" class="form-label">Adresse</label>
        </div>
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="%%fnPrefix%%_zip" name="zip" placeholder="PLZ"></input>
            <label for="%%fnPrefix%%_zip" class="form-label">PLZ</label>
        </div>
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="%%fnPrefix%%_city" name="city" placeholder="Ort"></input>
            <label for="%%fnPrefix%%_city" class="form-label">Ort</label>
        </div>
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="%%fnPrefix%%_country" name="country" placeholder="Land"></input>
            <label for="%%fnPrefix%%_country" class="form-label">Land</label>
        </div>
        EOT;
        return str_replace('%%fnPrefix%%', $fnPrefix, $htmlMemberAddressDetails);
    }

    function getMemberBankDetails($fnPrefix)
    {
        $htmlMemberBankDetails = <<<'EOT'
        <div class="form-floating">
        <select class="form-select" id="%%fnPrefix%%_invoice_marker" name="invoice_marker" aria-label="Floating label select example">
            <option value="0" selected>Abbuchung</option>
            <option value="1">Rechnungsstellung (keine Abbuchung)</option>
        </select>
        <label for="%%fnPrefix%%_invoice_marker">Rechnungsstellung oder Abbuchung</label>
        </div>
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="%%fnPrefix%%_bank_holder" name="bank_holder" placeholder="Kontoinhaber"></input>
            <label for="%%fnPrefix%%_bank_holder" class="form-label">Kontoinhaber</label>
        </div>
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="%%fnPrefix%%_bank_name" name="bank_name" placeholder="Name der Bank"></input>
            <label for="%%fnPrefix%%_bank_name" class="form-label">Name der Bank</label>
        </div>
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="%%fnPrefix%%_bank_bic" name="bank_bic" placeholder="BIC"></input>
            <label for="%%fnPrefix%%_bank_bic" class="form-label">BIC</label>
        </div>
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="%%fnPrefix%%_bank_iban" name="bank_iban" placeholder="IBAN"></input>
            <label for="%%fnPrefix%%_bank_iban" class="form-label">IBAN</label>
        </div>
        EOT;
        return str_replace('%%fnPrefix%%', $fnPrefix, $htmlMemberBankDetails);
    }
    
    function getMemberModalHtml($fnPrefix)
    {
        $htmlModal = getMemberModal($fnPrefix);
        $htmlModal = str_replace('%%ReplaceWithMemberDetails%%', getMemberDetails($fnPrefix), $htmlModal);
        $htmlModal = str_replace('%%ReplaceWithMemberAddressDetails%%', getMemberAddressDetails($fnPrefix), $htmlModal);
        $htmlModal = str_replace('%%ReplaceWithMemberBankDetails%%', getMemberBankDetails($fnPrefix), $htmlModal);
        $htmlModal = str_replace('%%ReplaceWithMemberTabHeader%%', getMemberTabHeader($fnPrefix), $htmlModal);
        $htmlModal = str_replace('%%ReplaceWithMemberModalAction%%', $fnPrefix == 'add' ? getMemberModalCreate() : getMemberModalUpdate(), $htmlModal);
        return str_replace('%%fnTitle%%', $fnPrefix == 'add' ? 'Neues Mitglied hinzufügen' : 'Mitglied bearbeiten', $htmlModal);
    }
?>