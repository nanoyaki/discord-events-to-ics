{
  lib,
  php84,
}:

php84.buildComposerProject {
  src = lib.cleanSource ./..;

  pname = "discord-events-to-ics";
  version = "0.2.2";

  php = php84.buildEnv {
    extensions = (
      { enabled, all }:
      enabled
      ++ (with all; [
        mongodb
      ])
    );
  };

  vendorHash = "sha256-frCelE1OWWiSBugENjRHOkMgS8NCRFte0CufyR5fi0A=";
}
